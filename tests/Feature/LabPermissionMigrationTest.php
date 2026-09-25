<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LabPermissionMigrationTest extends TestCase
{
    use RefreshDatabase;

    private function runLabPermissionMigration(): void
    {
        $migration = include database_path('migrations/2026_09_25_000002_create_lab_feature_permissions.php');
        $migration->up();
    }

    public function test_permissions_exist_after_migrate_without_seeder(): void
    {
        // RefreshDatabase hanya menjalankan migration — tidak ada seeder sama sekali.
        $this->assertDatabaseHas('permissions', ['name' => 'manage-lab-usages']);
        $this->assertDatabaseHas('permissions', ['name' => 'manage-lab-schedules']);
    }

    public function test_migration_attaches_permissions_to_existing_roles(): void
    {
        // Simulasi server lama: roles sudah ada, permission belum pernah dibuat.
        DB::table('permissions')->whereIn('name', ['manage-lab-usages', 'manage-lab-schedules'])->delete();

        $admin = Role::create(['name' => 'admin', 'label' => 'Admin']);
        $laboran = Role::create(['name' => 'laboran', 'label' => 'Laboran']);

        $this->runLabPermissionMigration();

        $adminPermissions = $admin->permissions()->pluck('name');
        $this->assertTrue($adminPermissions->contains('manage-lab-usages'));
        $this->assertTrue($adminPermissions->contains('manage-lab-schedules'));

        $laboranPermissions = $laboran->permissions()->pluck('name');
        $this->assertTrue($laboranPermissions->contains('manage-lab-usages'));
        $this->assertFalse($laboranPermissions->contains('manage-lab-schedules'));
    }

    public function test_migration_is_idempotent_when_permissions_already_exist(): void
    {
        $this->runLabPermissionMigration();
        $this->runLabPermissionMigration();

        $this->assertSame(1, Permission::where('name', 'manage-lab-usages')->count());
        $this->assertSame(1, Permission::where('name', 'manage-lab-schedules')->count());
    }
}
