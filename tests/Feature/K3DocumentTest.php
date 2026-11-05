<?php

namespace Tests\Feature;

use App\Models\K3Document;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class K3DocumentTest extends TestCase
{
    use RefreshDatabase;

    private function userWith(array $permissions, string $email): User
    {
        $role = Role::firstOrCreate(['name' => 'role-'.md5($email)], ['label' => 'Role Uji']);
        foreach ($permissions as $name) {
            $permission = Permission::firstOrCreate(['name' => $name], ['label' => $name]);
            $role->permissions()->syncWithoutDetaching([$permission->id]);
        }

        $user = User::firstOrCreate(
            ['email' => $email],
            ['name' => 'User Dokumen', 'password' => 'rahasia123']
        );
        $user->roles()->syncWithoutDetaching([$role->id]);

        return $user;
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('k3-documents.index'))->assertRedirect(route('login'));
    }

    public function test_sidebar_contains_menu_link(): void
    {
        $user = $this->userWith(['view-k3-documents'], 'menu-k3doc@uji.test');

        $this->actingAs($user)
            ->get(route('k3-documents.index'))
            ->assertOk()
            ->assertSee('href="'.route('k3-documents.index').'"', false)
            ->assertSee('Dokumen K3 Laboratorium');
    }

    public function test_sidebar_hides_menu_without_permission(): void
    {
        $user = $this->userWith([], 'tanpa-menu-k3doc@uji.test');

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee(route('k3-documents.index'));
    }

    public function test_can_upload_document(): void
    {
        Storage::fake('public');
        $user = $this->userWith(['create-k3-documents', 'view-k3-documents'], 'upload-k3doc@uji.test');

        $this->actingAs($user)
            ->post(route('k3-documents.store'), [
                'judul' => 'SOP K3 Laboratorium',
                'kategori' => 'sop',
                'nomor_dokumen' => 'SOP/K3/01',
                'file' => UploadedFile::fake()->create('sop-k3.pdf', 100, 'application/pdf'),
            ])
            ->assertRedirect(route('k3-documents.index'))
            ->assertSessionHas('success');

        $doc = K3Document::first();
        $this->assertNotNull($doc);
        $this->assertStringStartsWith('DOK-K3-', $doc->kode);
        $this->assertSame('SOP K3 Laboratorium', $doc->judul);
        $this->assertSame($user->id, $doc->uploaded_by);
        Storage::disk('public')->assertExists($doc->file_path);
    }

    public function test_can_update_document_metadata(): void
    {
        Storage::fake('public');
        $user = $this->userWith(['edit-k3-documents'], 'edit-k3doc@uji.test');
        $doc = K3Document::create([
            'judul' => 'Form Lama',
            'kategori' => 'form',
            'file_name' => 'form.pdf',
            'file_path' => 'k3-documents/test.pdf',
            'file_extension' => 'pdf',
            'file_size' => 1024,
        ]);

        $this->actingAs($user)
            ->put(route('k3-documents.update', $doc), [
                'judul' => 'Form Baru',
                'kategori' => 'form',
                'nomor_dokumen' => 'FORM/001',
            ])
            ->assertRedirect(route('k3-documents.index'));

        $doc->refresh();
        $this->assertSame('Form Baru', $doc->judul);
        $this->assertSame('FORM/001', $doc->nomor_dokumen);
    }

    public function test_can_download_document(): void
    {
        Storage::fake('public');
        $user = $this->userWith(['view-k3-documents'], 'download-k3doc@uji.test');
        $path = 'k3-documents/test.pdf';
        Storage::disk('public')->put($path, 'test content');

        $doc = K3Document::create([
            'judul' => 'Dokumen Test',
            'kategori' => 'lain-lain',
            'file_name' => 'test.pdf',
            'file_path' => $path,
            'file_extension' => 'pdf',
            'file_size' => 12,
        ]);

        $this->actingAs($user)
            ->get(route('k3-documents.download', $doc))
            ->assertOk();
    }

    public function test_can_delete_document(): void
    {
        Storage::fake('public');
        $user = $this->userWith(['delete-k3-documents'], 'delete-k3doc@uji.test');
        $path = 'k3-documents/to-delete.pdf';
        Storage::disk('public')->put($path, 'x');

        $doc = K3Document::create([
            'judul' => 'Hapus Ini',
            'kategori' => 'lain-lain',
            'file_name' => 'to-delete.pdf',
            'file_path' => $path,
            'file_extension' => 'pdf',
            'file_size' => 1,
        ]);

        $this->actingAs($user)
            ->delete(route('k3-documents.destroy', $doc))
            ->assertRedirect(route('k3-documents.index'));

        $this->assertDatabaseMissing('k3_documents', ['id' => $doc->id]);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_routes_forbidden_without_permission(): void
    {
        Storage::fake('public');
        $user = $this->userWith([], 'tanpa-k3doc@uji.test');
        $path = 'k3-documents/x.pdf';
        Storage::disk('public')->put($path, 'x');
        $doc = K3Document::create([
            'judul' => 'Test',
            'kategori' => 'lain-lain',
            'file_name' => 'x.pdf',
            'file_path' => $path,
            'file_extension' => 'pdf',
            'file_size' => 1,
        ]);

        $this->actingAs($user)->get(route('k3-documents.index'))->assertForbidden();
        $this->actingAs($user)->get(route('k3-documents.create'))->assertForbidden();
        $this->actingAs($user)->get(route('k3-documents.download', $doc))->assertForbidden();
        $this->actingAs($user)->delete(route('k3-documents.destroy', $doc))->assertForbidden();
    }
}
