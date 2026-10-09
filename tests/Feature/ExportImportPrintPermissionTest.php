<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ExportImportPrintPermissionTest extends TestCase
{
    use RefreshDatabase;

    private const NEW_PERMISSIONS = [
        'export-users', 'export-roles', 'export-laboratories', 'export-academic-years',
        'export-hardware', 'export-software', 'export-components', 'export-computers',
        'export-maintenance', 'export-tickets', 'export-borrowings', 'export-lab-usages',
        'export-lab-schedules',
        'import-hardware', 'import-software', 'import-components', 'import-computers',
        'import-maintenance', 'import-lab-schedules',
        'print-components', 'print-computers', 'print-maintenance', 'print-lab-usages',
        'print-reports',
    ];

    private function runMigration(string $step = 'up'): void
    {
        $migration = include database_path('migrations/2026_10_09_000001_add_export_import_print_permissions.php');
        $migration->{$step}();
    }

    private function userWith(array $permissions, string $email = 'uji@export.test'): User
    {
        $role = Role::firstOrCreate(['name' => 'role-'.md5($email)], ['label' => 'Role Uji']);

        foreach ($permissions as $name) {
            $role->permissions()->attach(
                Permission::firstOrCreate(['name' => $name], ['label' => $name])
            );
        }

        $user = User::create(['name' => 'User Uji', 'email' => $email, 'password' => 'rahasia123']);
        $user->roles()->attach($role);

        return $user;
    }

    public function test_all_export_import_print_permissions_exist_after_migrate(): void
    {
        foreach (self::NEW_PERMISSIONS as $name) {
            $this->assertDatabaseHas('permissions', ['name' => $name]);
        }

        $this->assertSame(count(self::NEW_PERMISSIONS), Permission::whereIn('name', self::NEW_PERMISSIONS)->count());
    }

    public function test_migration_backfills_from_old_source_permissions(): void
    {
        // Simulasi server lama: permission baru belum ada, role sudah punya izin lama.
        DB::table('role_has_permissions')->whereIn('permission_id', fn ($q) => $q->select('id')
            ->from('permissions')->whereIn('name', self::NEW_PERMISSIONS))->delete();
        DB::table('permissions')->whereIn('name', self::NEW_PERMISSIONS)->delete();

        $legacy = Role::create(['name' => 'legacy', 'label' => 'Legacy']);
        foreach ([
            'view-users', 'view-computers', 'edit-computers', 'create-hardware',
            'view-reports', 'create-lab-schedules', 'view-lab-usages',
        ] as $name) {
            $legacy->permissions()->attach(
                Permission::firstOrCreate(['name' => $name], ['label' => $name])
            );
        }

        $viewOnly = Role::create(['name' => 'view-only', 'label' => 'View Only']);
        $viewOnly->permissions()->attach(
            Permission::firstOrCreate(['name' => 'view-components'], ['label' => 'view-components'])
        );

        $this->runMigration();

        $names = $legacy->permissions()->pluck('name');
        // view-* lama -> export-* & print-* baru
        $this->assertTrue($names->contains('export-users'));
        $this->assertTrue($names->contains('export-computers'));
        $this->assertTrue($names->contains('print-computers'));
        $this->assertTrue($names->contains('print-reports'));
        $this->assertTrue($names->contains('print-lab-usages'));
        // edit-computers -> import-computers, create-hardware -> import-hardware
        $this->assertTrue($names->contains('import-computers'));
        $this->assertTrue($names->contains('import-hardware'));
        // create-lab-schedules -> import-lab-schedules
        $this->assertTrue($names->contains('import-lab-schedules'));
        // view-components tidak memberi import-components
        $this->assertFalse($names->contains('import-components'));

        $viewNames = $viewOnly->permissions()->pluck('name');
        $this->assertTrue($viewNames->contains('export-components'));
        $this->assertTrue($viewNames->contains('print-components'));
        $this->assertFalse($viewNames->contains('import-components'));
    }

    public function test_migration_is_idempotent_and_down_removes_permissions(): void
    {
        $this->runMigration();
        $this->runMigration();

        foreach (self::NEW_PERMISSIONS as $name) {
            $this->assertSame(1, Permission::where('name', $name)->count());
        }

        $this->runMigration('down');

        foreach (self::NEW_PERMISSIONS as $name) {
            $this->assertDatabaseMissing('permissions', ['name' => $name]);
        }
    }

    public function test_export_routes_require_export_permission(): void
    {
        $viewer = $this->userWith(['view-users'], 'viewer-export@test');
        $exporter = $this->userWith(['export-users'], 'exporter@test');

        $this->actingAs($viewer)->get(route('users.export'))->assertForbidden();
        $this->actingAs($exporter)->get(route('users.export'))->assertOk();
    }

    public function test_import_routes_require_import_permission(): void
    {
        $viewer = $this->userWith(['view-hardware'], 'viewer-import@test');
        $importer = $this->userWith(['import-hardware'], 'importer@test');

        $this->actingAs($viewer)->get(route('hardware.import'))->assertForbidden();
        $this->actingAs($importer)->get(route('hardware.import'))->assertOk();
    }

    public function test_print_routes_require_print_permission(): void
    {
        $viewer = $this->userWith(['view-computers'], 'viewer-print@test');
        $printer = $this->userWith(['print-computers'], 'printer@test');

        $this->actingAs($viewer)->get(route('computers.qr-stiker'))->assertForbidden();
        $this->actingAs($printer)->get(route('computers.qr-stiker'))->assertOk();
    }

    public function test_template_route_accepts_export_or_import_permission(): void
    {
        $exporter = $this->userWith(['export-computers'], 'tmpl-export@test');
        $importer = $this->userWith(['import-computers'], 'tmpl-import@test');
        $viewer = $this->userWith(['view-computers'], 'tmpl-view@test');

        $this->actingAs($exporter)->get(route('computers.spec-template'))->assertOk();
        $this->actingAs($importer)->get(route('computers.spec-template'))->assertOk();
        $this->actingAs($viewer)->get(route('computers.spec-template'))->assertForbidden();
    }

    public function test_index_page_hides_export_and_import_buttons_without_permission(): void
    {
        $viewer = $this->userWith(['view-hardware'], 'btn-view@test');
        $exporter = $this->userWith(['view-hardware', 'export-hardware'], 'btn-export@test');
        $importer = $this->userWith(['view-hardware', 'import-hardware'], 'btn-import@test');

        $this->actingAs($viewer)
            ->get(route('hardware.index'))
            ->assertOk()
            ->assertDontSee(route('hardware.export'), false)
            ->assertDontSee(route('hardware.import'), false);

        $this->actingAs($exporter)
            ->get(route('hardware.index'))
            ->assertOk()
            ->assertSee(route('hardware.export'), false)
            ->assertDontSee(route('hardware.import'), false);

        $this->actingAs($importer)
            ->get(route('hardware.index'))
            ->assertOk()
            ->assertSee(route('hardware.import'), false)
            ->assertDontSee(route('hardware.export'), false);
    }

    public function test_role_form_shows_export_import_print_actions(): void
    {
        $admin = $this->userWith(['create-roles', 'view-roles'], 'role-form@test');

        $this->actingAs($admin)
            ->get(route('roles.create'))
            ->assertOk()
            ->assertSee('export-computers', false)
            ->assertSee('import-computers', false)
            ->assertSee('print-computers', false)
            ->assertSee('Cetak');
    }
}
