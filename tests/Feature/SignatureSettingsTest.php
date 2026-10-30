<?php

namespace Tests\Feature;

use App\Models\Computer;
use App\Models\Laboratory;
use App\Models\Permission;
use App\Models\ReportSignature;
use App\Models\Role;
use App\Models\Signature;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SignatureSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Signature::flushReportCache();
    }

    private function userWith(string $email, array $permissions): User
    {
        $role = Role::firstOrCreate(['name' => 'role-'.md5($email)], ['label' => 'Role Uji']);

        foreach ($permissions as $permission) {
            $role->permissions()->attach(
                Permission::firstOrCreate(['name' => $permission], ['label' => $permission])
            );
        }

        $user = User::create([
            'name' => 'User Uji',
            'email' => $email,
            'password' => 'rahasia123',
        ]);
        $user->roles()->attach($role);

        return $user;
    }

    private function computer(): Computer
    {
        $laboratory = Laboratory::create([
            'name' => 'Lab Komputer 1',
            'code' => 'LK1',
            'location' => 'Gedung A',
            'capacity' => 40,
        ]);

        return Computer::create([
            'code' => 'K1-001',
            'laboratory_id' => $laboratory->id,
            'status' => 'Aktif',
        ]);
    }

    /** PNG transparan1×1 valid untuk aturan validasi image (tanpa dependensi GD). */
    private function tinyPng(): string
    {
        return base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==');
    }

    public function test_settings_memerlukan_izin_laporan(): void
    {
        $tanpaIzin = $this->userWith('ttd-none@uji.test', ['view-dashboard']);
        $lihatTtd = $this->userWith('ttd-view@uji.test', ['view-signatures']);
        $kelolaTtd = $this->userWith('ttd-manage@uji.test', ['view-signatures', 'create-signatures', 'edit-signatures']);

        $this->actingAs($tanpaIzin)->get(route('settings.signatures.index'))->assertForbidden();
        $this->actingAs($tanpaIzin)->get(route('settings.signatures.create'))->assertForbidden();

        $this->actingAs($lihatTtd)
            ->get(route('settings.signatures.index'))
            ->assertOk()
            ->assertDontSee('Tambah TTD');
        $this->actingAs($lihatTtd)->get(route('settings.signatures.create'))->assertForbidden();

        $this->actingAs($kelolaTtd)
            ->get(route('settings.signatures.index'))
            ->assertOk()
            ->assertSee('Tambah TTD');
        $this->actingAs($kelolaTtd)->get(route('settings.signatures.create'))->assertOk();
    }

    public function test_crud_ttd_dengan_gambar_dan_penugasan_laporan(): void
    {
        Storage::fake('public');
        $admin = $this->userWith('ttd-crud@uji.test', ['view-signatures', 'create-signatures', 'edit-signatures', 'delete-signatures']);

        $this->actingAs($admin)
            ->post(route('settings.signatures.store'), [
                'name' => 'Nama TTD Uji',
                'role' => 'Jabatan TTD Uji',
                'image' => UploadedFile::fake()->createWithContent('ttd.png', $this->tinyPng()),
                'reports' => ['card-print', 'laporan'],
            ])
            ->assertRedirect(route('settings.signatures.index'))
            ->assertSessionHas('success');

        $signature = Signature::first();
        $this->assertNotNull($signature);
        $this->assertSame('Nama TTD Uji', $signature->name);
        $this->assertNotEmpty($signature->image);
        Storage::disk('public')->assertExists($signature->image);
        $this->assertSame(
            ['card-print', 'laporan'],
            ReportSignature::orderBy('report_key')->pluck('report_key')->all()
        );

        $this->actingAs($admin)
            ->get(route('settings.signatures.edit', $signature))
            ->assertOk()
            ->assertSee('Nama TTD Uji')
            ->assertSee('value="card-print" checked', false);

        // TTD kedua mengambil alih laporan 'laporan' (report_key unique).
        $this->actingAs($admin)
            ->post(route('settings.signatures.store'), [
                'name' => 'TTD Kedua',
                'role' => 'Jabatan Kedua',
                'reports' => ['laporan'],
            ])
            ->assertRedirect(route('settings.signatures.index'));

        $this->assertSame(
            ['card-print', 'laporan'],
            ReportSignature::orderBy('report_key')->pluck('report_key')->sort()->values()->all()
        );
        $this->assertSame(
            ['card-print' => $signature->id, 'laporan' => Signature::where('name', 'TTD Kedua')->value('id')],
            ReportSignature::pluck('signature_id', 'report_key')->map(fn ($id) => (int) $id)->all()
        );

        // Update: lepas semua penugasan TTD pertama, TTD kedua masih memegang 'laporan'.
        $this->actingAs($admin)
            ->put(route('settings.signatures.update', $signature), [
                'name' => 'Nama Diubah',
                'role' => 'Jabatan Diubah',
                'reports' => [],
            ])
            ->assertRedirect(route('settings.signatures.index'));

        $this->assertSame('Nama Diubah', $signature->fresh()->name);
        $this->assertSame(
            ['laporan'],
            ReportSignature::pluck('report_key')->all()
        );

        // Hapus TTD kedua: penugasan 'laporan' ikut terhapus (FK cascade, laporan kembali default).
        $second = Signature::where('name', 'TTD Kedua')->firstOrFail();
        $this->actingAs($admin)
            ->delete(route('settings.signatures.destroy', $second))
            ->assertRedirect(route('settings.signatures.index'));

        $this->assertSame(0, ReportSignature::count());

        // Hapus TTD pertama (sudah tanpa penugasan): file gambar ikut terhapus.
        $image = $signature->fresh()->image;
        $this->actingAs($admin)
            ->delete(route('settings.signatures.destroy', $signature))
            ->assertRedirect(route('settings.signatures.index'));

        $this->assertNull(Signature::find($signature->id));
        $this->assertSame(0, Signature::count());
        Storage::disk('public')->assertMissing($image);
    }

    public function test_cetak_kartu_kendali_pakai_ttd_default_lalu_ttd_terpilih(): void
    {
        Storage::fake('public');
        $printer = $this->userWith('ttd-card-print@uji.test', ['print-computers']);
        $computer = $this->computer();

        // Tanpa TTD terpilih: memakai TTD default hardcoded laporan.
        $this->actingAs($printer)
            ->get(route('computers.card-print', $computer))
            ->assertOk()
            ->assertSee('Firmansyah, S.Kom')
            ->assertDontSee('Nama TTD Uji')
            ->assertDontSee('data:image/');

        Signature::flushReportCache();

        $signature = Signature::create([
            'name' => 'Nama TTD Uji',
            'role' => 'Jabatan TTD Uji',
            'image' => tap(
                'signatures/uji.png',
                fn ($path) => Storage::disk('public')->put($path, $this->tinyPng())
            ),
        ]);
        ReportSignature::create([
            'report_key' => 'card-print',
            'signature_id' => $signature->id,
        ]);

        // TTD terpilih menggantikan default: nama, jabatan, dan gambar base64 ikut tercetak.
        $this->actingAs($printer)
            ->get(route('computers.card-print', $computer))
            ->assertOk()
            ->assertSee('Nama TTD Uji')
            ->assertSee('Jabatan TTD Uji')
            ->assertSee('data:image/png;base64,', false)
            ->assertDontSee('Firmansyah, S.Kom');
    }
}
