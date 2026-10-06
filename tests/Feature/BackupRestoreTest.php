<?php

namespace Tests\Feature;

use App\Models\Laboratory;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\DatabaseBackupService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class BackupRestoreTest extends TestCase
{
    use RefreshDatabase;

    /** @var list<string> */
    private array $preExistingBackupFiles = [];

    protected function setUp(): void
    {
        parent::setUp();

        $dir = DatabaseBackupService::backupDirectory();
        $this->preExistingBackupFiles = is_dir($dir) ? (glob($dir.'/backup-*.json') ?: []) : [];
    }

    protected function tearDown(): void
    {
        $dir = DatabaseBackupService::backupDirectory();
        foreach (glob($dir.'/backup-*.json') ?: [] as $path) {
            if (! in_array($path, $this->preExistingBackupFiles, true)) {
                @unlink($path);
            }
        }

        parent::tearDown();
    }

    private function admin(): User
    {
        $role = Role::firstOrCreate(['name' => 'admin'], ['label' => 'Admin']);
        $role->syncPermissions(
            Permission::whereIn('name', ['view-backups', 'edit-backups', 'delete-backups'])->get()
        );
        $user = User::firstOrCreate(['email' => 'admin@backup.test'], ['name' => 'Admin', 'password' => 'rahasia123']);
        $user->roles()->syncWithoutDetaching([$role->id]);

        return $user;
    }

    private function plainUser(): User
    {
        // Tanpa role maupun permission apa pun.
        return User::firstOrCreate(['email' => 'plain@backup.test'], ['name' => 'User Biasa', 'password' => 'rahasia123']);
    }

    private function lab(string $code): Laboratory
    {
        return Laboratory::create([
            'name' => 'Lab '.$code,
            'code' => $code,
            'location' => 'Gedung A',
            'capacity' => 20,
        ]);
    }

    public function test_admin_can_download_backup_json(): void
    {
        $this->lab('BK1');

        $response = $this->actingAs($this->admin())->get(route('backups.download'));

        $response->assertOk();
        $this->assertSame('application/json', (string) $response->headers->get('Content-Type'));

        $payload = json_decode($response->getContent(), true);

        $this->assertIsArray($payload);
        $this->assertSame(DatabaseBackupService::FORMAT_VERSION, $payload['format_version']);
        $this->assertArrayHasKey('tables', $payload);
        $this->assertArrayHasKey('laboratories', $payload['tables']);
        $this->assertSame('BK1', $payload['tables']['laboratories'][0]['code']);

        foreach (DatabaseBackupService::EXCLUDED_TABLES as $excluded) {
            $this->assertArrayNotHasKey($excluded, $payload['tables']);
        }
    }

    public function test_backup_routes_require_permission(): void
    {
        $this->get(route('backups.index'))->assertRedirect(route('login'));
        $this->get(route('backups.download'))->assertRedirect(route('login'));

        // User tanpa role/permission TIDAK boleh mengakses backup & restore.
        $user = $this->plainUser();

        $this->actingAs($user)->get(route('backups.index'))->assertForbidden();
        $this->actingAs($user)->get(route('backups.download'))->assertForbidden();

        // Menu sidebar backup juga tidak tampil untuk user tanpa permission.
        $this->actingAs($user)->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee(route('backups.index'), false);

        // User dengan permission view-backups tetap bisa akses.
        $this->actingAs($this->admin())->get(route('backups.index'))
            ->assertOk()
            ->assertSee('Backup & Restore', false);
    }

    public function test_restore_round_trip_replaces_all_data(): void
    {
        $admin = $this->admin();
        $this->lab('BK1');
        $this->lab('BK2');

        $service = app(DatabaseBackupService::class);
        $payload = $service->export();

        // Ubah data setelah backup diambil.
        Laboratory::where('code', 'BK2')->delete();
        $this->lab('BK3');

        $this->assertSame(2, Laboratory::count());

        $this->actingAs($admin)
            ->from(route('backups.index'))
            ->post(route('backups.restore'), [
                'file' => UploadedFile::fake()->createWithContent('backup.json', json_encode($payload)),
                'confirmation' => 'RESTORE',
            ])
            ->assertRedirect(route('backups.index'))
            ->assertSessionHas('success');

        $this->assertSame(2, Laboratory::count());
        $this->assertDatabaseHas('laboratories', ['code' => 'BK1']);
        $this->assertDatabaseHas('laboratories', ['code' => 'BK2']);
        $this->assertDatabaseMissing('laboratories', ['code' => 'BK3']);

        // Akun admin ikut direstore sehingga sesi login tetap hidup.
        $this->assertAuthenticatedAs($admin);
    }

    public function test_restore_rejects_invalid_json_and_wrong_format(): void
    {
        $admin = $this->admin();
        $this->lab('BK1');
        $before = Laboratory::count();

        // JSON valid tetapi struktur bukan backup.
        $this->actingAs($admin)
            ->from(route('backups.index'))
            ->post(route('backups.restore'), [
                'file' => UploadedFile::fake()->createWithContent('backup.json', '{"foo":"bar"}'),
                'confirmation' => 'RESTORE',
            ])
            ->assertRedirect(route('backups.index'))
            ->assertSessionHas('error');

        // Bukan JSON sama sekali.
        $this->actingAs($admin)
            ->from(route('backups.index'))
            ->post(route('backups.restore'), [
                'file' => UploadedFile::fake()->createWithContent('backup.json', '{"unclosed": '),
                'confirmation' => 'RESTORE',
            ])
            ->assertRedirect(route('backups.index'))
            ->assertSessionHas('error');

        // Kolom tidak sesuai struktur database.
        $this->actingAs($admin)
            ->from(route('backups.index'))
            ->post(route('backups.restore'), [
                'file' => UploadedFile::fake()->createWithContent('backup.json', json_encode([
                    'format_version' => DatabaseBackupService::FORMAT_VERSION,
                    'tables' => ['laboratories' => [['id' => 'x', 'kolom_tidak_ada' => '1']]],
                ])),
                'confirmation' => 'RESTORE',
            ])
            ->assertRedirect(route('backups.index'))
            ->assertSessionHas('error');

        $this->assertSame($before, Laboratory::count());
        $this->assertDatabaseHas('laboratories', ['code' => 'BK1']);
    }

    public function test_restore_rejects_backup_with_dropped_academic_year_date_columns(): void
    {
        $admin = $this->admin();
        $this->lab('BK1');
        $before = Laboratory::count();

        // Backup yang dibuat sebelum kolom start_date/end_date dihapus dari
        // academic_years: sengaja ditolak agar data tidak rusak.
        $this->actingAs($admin)
            ->from(route('backups.index'))
            ->post(route('backups.restore'), [
                'file' => UploadedFile::fake()->createWithContent('backup.json', json_encode([
                    'format_version' => DatabaseBackupService::FORMAT_VERSION,
                    'tables' => ['academic_years' => [[
                        'id' => 'x',
                        'name' => 'Tahun Ajaran 2026/2027',
                        'start_year' => 2026,
                        'end_year' => 2027,
                        'status' => 'Aktif',
                        'start_date' => '2026-08-01',
                        'end_date' => '2027-06-30',
                    ]]],
                ])),
                'confirmation' => 'RESTORE',
            ])
            ->assertRedirect(route('backups.index'))
            ->assertSessionHas('error');

        $this->assertSame($before, Laboratory::count());
        $this->assertDatabaseHas('laboratories', ['code' => 'BK1']);
    }

    public function test_restore_requires_exact_confirmation(): void
    {
        $admin = $this->admin();
        $this->lab('BK1');

        $payload = app(DatabaseBackupService::class)->export();

        $this->actingAs($admin)
            ->from(route('backups.index'))
            ->post(route('backups.restore'), [
                'file' => UploadedFile::fake()->createWithContent('backup.json', json_encode($payload)),
                'confirmation' => 'restore',
            ])
            ->assertRedirect(route('backups.index'))
            ->assertSessionHas('error');

        // Data tidak tersentuh.
        $this->assertDatabaseHas('laboratories', ['code' => 'BK1']);
        $this->assertSame(1, Laboratory::count());
    }

    public function test_backup_command_creates_file_and_keeps_existing_files(): void
    {
        $this->lab('BK1');

        $dir = DatabaseBackupService::backupDirectory();
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $oldFile = $dir.'/backup-2020-01-01-000000.json';
        file_put_contents($oldFile, '{}');
        $this->assertFileExists($oldFile);

        $this->artisan('backup:run')->assertSuccessful();

        $newFiles = array_diff(glob($dir.'/backup-*.json') ?: [], [$oldFile]);
        $this->assertNotEmpty($newFiles, 'Perintah backup:run tidak menghasilkan file backup.');

        // Retention tanpa auto-hapus: file lama tetap ada.
        $this->assertFileExists($oldFile);

        $payload = json_decode((string) file_get_contents(head($newFiles)), true);
        $this->assertSame(DatabaseBackupService::FORMAT_VERSION, $payload['format_version']);
        $this->assertArrayHasKey('laboratories', $payload['tables']);

        @unlink($oldFile);
    }

    public function test_auto_backup_file_download_delete_and_traversal_protection(): void
    {
        $dir = DatabaseBackupService::backupDirectory();
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $file = 'backup-2026-01-01-000000.json';
        file_put_contents($dir.'/'.$file, '{"format_version": 1, "tables": {}}');

        $admin = $this->admin();

        $this->actingAs($admin)
            ->get(route('backups.files.download', $file))
            ->assertOk()
            ->assertDownload($file);

        $this->actingAs($admin)
            ->get(route('backups.index'))
            ->assertOk()
            ->assertSee($file);

        // Nama file di luar pola backup-*.json ditolak (404).
        $this->actingAs($admin)
            ->get(route('backups.files.download', ['filename' => '..%2F..%2F.env']))
            ->assertNotFound();

        $this->actingAs($admin)
            ->get(route('backups.files.download', 'backup-2099-01-01-000000.json'))
            ->assertNotFound();

        $this->actingAs($admin)
            ->from(route('backups.index'))
            ->delete(route('backups.files.destroy', $file))
            ->assertRedirect(route('backups.index'))
            ->assertSessionHas('success');

        $this->assertFileDoesNotExist($dir.'/'.$file);
    }
}
