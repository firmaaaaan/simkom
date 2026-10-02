<?php

namespace Tests\Feature;

use App\Models\Computer;
use App\Models\ComputerBorrowing;
use App\Models\Laboratory;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BorrowingMarkReturnedTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $role = Role::firstOrCreate(['name' => 'admin-uji'], ['label' => 'Admin Uji']);
        foreach (['view-borrowings', 'edit-borrowings'] as $name) {
            $permission = Permission::firstOrCreate(['name' => $name], ['label' => $name]);
            $role->permissions()->syncWithoutDetaching([$permission->id]);
        }

        $user = User::firstOrCreate(
            ['email' => 'admin-peminjaman@uji.test'],
            ['name' => 'Admin Peminjaman', 'password' => 'rahasia123']
        );
        $user->roles()->syncWithoutDetaching([$role->id]);

        return $user;
    }

    private function borrowing(string $status): ComputerBorrowing
    {
        $lab = Laboratory::create([
            'name' => 'Lab Uji Peminjaman', 'code' => 'LAB-UPJ', 'location' => 'Gedung Uji', 'capacity' => 10,
        ]);
        $computer = Computer::create([
            'code' => 'PC-UJI-PJ', 'laboratory_id' => $lab->id, 'status' => 'Aktif',
        ]);

        return ComputerBorrowing::create([
            'computer_id' => $computer->id,
            'laboratory_id' => $lab->id,
            'borrower_name' => 'Budi Santoso',
            'borrower_nim' => '220001',
            'borrower_prodi' => 'Teknik Informatika',
            'purpose' => 'Praktikum Jaringan Komputer',
            'borrow_date' => '2026-09-30',
            'borrow_time_start' => '08:00',
            'borrow_time_end' => '10:00',
            'status' => $status,
        ]);
    }

    public function test_index_shows_prodi_purpose_and_returned_columns(): void
    {
        $admin = $this->admin();
        $this->borrowing('Approved');

        $this->actingAs($admin)
            ->get(route('borrowings.index'))
            ->assertOk()
            ->assertSee('Prodi')
            ->assertSee('Teknik Informatika')
            ->assertSee('Keperluan')
            ->assertSee('Praktikum Jaringan')
            ->assertSee('Dikembalikan')
            ->assertSee('Tandai Sudah Keluar');
    }

    public function test_admin_can_mark_approved_borrowing_as_returned(): void
    {
        $admin = $this->admin();
        $borrowing = $this->borrowing('Approved');

        $this->actingAs($admin)
            ->from(route('borrowings.index'))
            ->patch(route('borrowings.mark-returned', $borrowing))
            ->assertRedirect(route('borrowings.index'))
            ->assertSessionHas('success');

        $borrowing->refresh();
        $this->assertSame('Returned', $borrowing->status);
        $this->assertNotNull($borrowing->returned_at);
        $this->assertSame($admin->id, $borrowing->returned_by);
    }

    public function test_pending_cannot_be_marked_returned(): void
    {
        $admin = $this->admin();
        $borrowing = $this->borrowing('Pending');

        $this->actingAs($admin)
            ->from(route('borrowings.index'))
            ->patch(route('borrowings.mark-returned', $borrowing))
            ->assertRedirect(route('borrowings.index'))
            ->assertSessionHasErrors('status');

        $borrowing->refresh();
        $this->assertSame('Pending', $borrowing->status);
        $this->assertNull($borrowing->returned_at);
    }

    public function test_mark_returned_requires_permission(): void
    {
        $user = User::create([
            'name' => 'Tanpa Izin',
            'email' => 'tanpa-izin@uji.test',
            'password' => 'rahasia123',
        ]);
        $borrowing = $this->borrowing('Approved');

        $this->actingAs($user)
            ->patch(route('borrowings.mark-returned', $borrowing))
            ->assertForbidden();

        $this->assertSame('Approved', $borrowing->refresh()->status);
    }
}
