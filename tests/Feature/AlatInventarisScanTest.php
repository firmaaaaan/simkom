<?php

namespace Tests\Feature;

use App\Models\Alat;
use App\Models\Laboratory;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AlatInventarisScanTest extends TestCase
{
    use RefreshDatabase;

    private function userWith(array $permissions): User
    {
        $role = Role::firstOrCreate(['name' => 'role-invscan-'.md5(implode(',', $permissions))], ['label' => 'Role Inventaris Scan']);
        foreach ($permissions as $p) {
            $role->permissions()->syncWithoutDetaching([
                Permission::firstOrCreate(['name' => $p], ['label' => $p])->id,
            ]);
        }

        $user = User::firstOrCreate(
            ['email' => 'invscan-'.md5(implode(',', $permissions)).'@uji.test'],
            ['name' => 'Uji Inv Scan', 'password' => 'rahasia123']
        );
        $user->roles()->syncWithoutDetaching($role->id);

        return $user;
    }

    private function lab(): Laboratory
    {
        return Laboratory::create([
            'code' => 'LAB-INV',
            'name' => 'Lab Inv Scan',
            'location' => 'Gedung S',
            'capacity' => 20,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function inventarisPayload(array $overrides = []): array
    {
        return array_merge([
            'sumber' => 'invent_v_barcode',
            'no_inventaris' => '2.08.01.01.20.506',
            'nama_alat' => 'Komputer',
            'merk_type' => "16 GB SSD 1 TB\tCore i7-14700f",
            'laboratorium' => 'Lab Riset TI digital Forensic',
            'kdruang' => 1336,
            'lokasi' => 'SM.1.14 / Lab Riset TI digital Forensic',
            'status' => 'Baik',
            'keterangan' => 'SSD 1 TB, Core i7-14700f',
        ], $overrides);
    }

    private function fakeScanSuccess(array $data): void
    {
        Http::fake([
            config('inventaris.scan_api_url').'*' => Http::response([
                'success' => true,
                'message' => 'Berhasil mengambil data inventaris',
                'data' => $data,
            ], 200),
        ]);
    }

    private function fakeScanNotFound(): void
    {
        Http::fake([
            config('inventaris.scan_api_url').'*' => Http::response([
                'success' => false,
                'message' => 'Inventaris dengan kode tersebut tidak ditemukan',
                'data' => null,
            ], 404),
        ]);
    }

    public function test_plain_code_scan_saves_alat_from_simptt_data(): void
    {
        $this->fakeScanSuccess($this->inventarisPayload());

        $response = $this->actingAs($this->userWith(['create-alat']))
            ->postJson(route('alat.scan-store'), [
                'laboratory_id' => $this->lab()->id,
                'code' => '2.08.01.01.20.506',
            ]);

        $response
            ->assertCreated()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('alat.code', '2.08.01.01.20.506')
            ->assertJsonPath('alat.name', 'Komputer')
            ->assertJsonPath('inventaris.laboratorium', 'Lab Riset TI digital Forensic');

        $this->assertStringContainsString('Lab: Lab Riset TI digital Forensic', $response->json('message'));

        $this->assertDatabaseHas('alat', [
            'code' => '2.08.01.01.20.506',
            'name' => 'Komputer',
            // Tab pada merk_type dari API dinormalisasi menjadi spasi.
            'merk_type' => '16 GB SSD 1 TB Core i7-14700f',
            'location' => 'SM.1.14 / Lab Riset TI digital Forensic',
            'status' => 'Baik',
            'description' => 'SSD 1 TB, Core i7-14700f',
        ]);
    }

    public function test_scan_not_found_in_simptt_returns_422_and_saves_nothing(): void
    {
        $this->fakeScanNotFound();

        $this->actingAs($this->userWith(['create-alat']))
            ->postJson(route('alat.scan-store'), [
                'laboratory_id' => $this->lab()->id,
                'code' => 'i147555',
            ])
            ->assertStatus(422)
            ->assertJson(['ok' => false]);

        $this->assertSame(0, Alat::count());
    }

    public function test_simptt_server_error_returns_502_and_saves_nothing(): void
    {
        Http::fake([
            config('inventaris.scan_api_url').'*' => Http::response('', 500),
        ]);

        $this->actingAs($this->userWith(['create-alat']))
            ->postJson(route('alat.scan-store'), [
                'laboratory_id' => $this->lab()->id,
                'code' => '2.08.01.01.20.506',
            ])
            ->assertStatus(502)
            ->assertJson(['ok' => false]);

        $this->assertSame(0, Alat::count());
    }

    public function test_simptt_connection_failure_returns_502_and_saves_nothing(): void
    {
        Http::fake(function () {
            throw new ConnectionException('Connection refused');
        });

        $this->actingAs($this->userWith(['create-alat']))
            ->postJson(route('alat.scan-store'), [
                'laboratory_id' => $this->lab()->id,
                'code' => '2.08.01.01.20.506',
            ])
            ->assertStatus(502)
            ->assertJson(['ok' => false]);

        $this->assertSame(0, Alat::count());
    }

    public function test_json_payload_with_name_skips_simptt_lookup(): void
    {
        Http::fake();

        $this->actingAs($this->userWith(['create-alat']))
            ->postJson(route('alat.scan-store'), [
                'laboratory_id' => $this->lab()->id,
                'code' => 'INV.JSON.001',
                'name' => 'Alat dari Barcode JSON',
                'merk_type' => 'Merek X',
            ])
            ->assertCreated()
            ->assertJsonPath('ok', true);

        Http::assertNothingSent();

        $this->assertDatabaseHas('alat', [
            'code' => 'INV.JSON.001',
            'name' => 'Alat dari Barcode JSON',
            'merk_type' => 'Merek X',
        ]);
    }

    public function test_duplicate_code_rejected_before_simptt_lookup(): void
    {
        $this->fakeScanSuccess($this->inventarisPayload());

        $alat = Alat::create([
            'laboratory_id' => $this->lab()->id,
            'name' => 'Komputer Lama',
            'code' => '2.08.01.01.20.506',
        ]);

        $this->actingAs($this->userWith(['create-alat']))
            ->postJson(route('alat.scan-store'), [
                'laboratory_id' => $alat->laboratory_id,
                'code' => '2.08.01.01.20.506',
            ])
            ->assertStatus(422)
            ->assertJson(['ok' => false]);

        Http::assertNothingSent();
        $this->assertSame(1, Alat::where('code', '2.08.01.01.20.506')->count());
    }

    public function test_scan_store_requires_create_permission(): void
    {
        Http::fake();

        $this->actingAs($this->userWith(['view-alat']))
            ->postJson(route('alat.scan-store'), [
                'laboratory_id' => $this->lab()->id,
                'code' => '2.08.01.01.20.506',
            ])
            ->assertForbidden();

        Http::assertNothingSent();
        $this->assertSame(0, Alat::count());
    }
}
