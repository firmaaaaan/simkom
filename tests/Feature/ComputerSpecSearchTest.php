<?php

namespace Tests\Feature;

use App\Models\Computer;
use App\Models\Hardware;
use App\Models\Laboratory;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class ComputerSpecSearchTest extends TestCase
{
    use RefreshDatabase;

    private Laboratory $labA;

    private Laboratory $labB;

    private Computer $pcIntel;

    private Computer $pcAmd;

    private Computer $pcNoSpec;

    protected function setUp(): void
    {
        parent::setUp();

        $this->labA = Laboratory::create(['name' => 'Lab Komputer 1', 'code' => 'LK1', 'location' => 'Gedung A', 'capacity' => 40]);
        $this->labB = Laboratory::create(['name' => 'Lab Jaringan', 'code' => 'LJ1', 'location' => 'Gedung B', 'capacity' => 32]);

        $this->pcIntel = Computer::create(['code' => 'SPEC-001', 'laboratory_id' => $this->labA->id, 'status' => 'Aktif']);
        $this->pcAmd = Computer::create(['code' => 'SPEC-002', 'laboratory_id' => $this->labB->id, 'status' => 'Aktif']);
        $this->pcNoSpec = Computer::create(['code' => 'SPEC-003', 'laboratory_id' => $this->labA->id, 'status' => 'Aktif']);

        $i5 = Hardware::create(['name' => 'Intel Core i5-12400', 'code' => 'HW-SP-001', 'brand' => 'Intel', 'model' => 'i5-12400', 'category' => 'Processor']);
        $ram8 = Hardware::create(['name' => '8GB DDR4', 'code' => 'HW-SP-002', 'brand' => 'Kingston', 'category' => 'RAM']);
        $ryzen = Hardware::create(['name' => 'AMD Ryzen 3 3200G', 'code' => 'HW-SP-003', 'brand' => 'AMD', 'category' => 'Processor']);

        $this->pcIntel->hardware()->attach([$i5->id, $ram8->id]);
        $this->pcAmd->hardware()->attach($ryzen->id);
    }

    private function userWith(array $permissions, string $email): User
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

    private function rows(TestResponse $response): array
    {
        $path = $response->baseResponse->getFile()->getPathname();

        return IOFactory::load($path)->getActiveSheet()->toArray(null, true, false, false);
    }

    public function test_keyword_search_matches_hardware_name_brand_model_or_category(): void
    {
        $user = $this->userWith(['view-computers'], 'viewer-spec@test');

        $this->actingAs($user)
            ->get(route('computers.index', ['spec' => 'i5']))
            ->assertOk()
            ->assertSee('SPEC-001')
            ->assertDontSee('SPEC-002')
            ->assertDontSee('SPEC-003');

        $this->actingAs($user)
            ->get(route('computers.index', ['spec' => 'kingston']))
            ->assertOk()
            ->assertSee('SPEC-001')
            ->assertDontSee('SPEC-002');

        $this->actingAs($user)
            ->get(route('computers.index', ['spec' => 'RAM']))
            ->assertOk()
            ->assertSee('SPEC-001')
            ->assertDontSee('SPEC-002')
            ->assertDontSee('SPEC-003');
    }

    public function test_category_filter_only_returns_computers_with_that_hardware_category(): void
    {
        $user = $this->userWith(['view-computers'], 'viewer-kat@test');

        $this->actingAs($user)
            ->get(route('computers.index', ['spec_category' => 'RAM']))
            ->assertOk()
            ->assertSee('SPEC-001')
            ->assertDontSee('SPEC-002')
            ->assertDontSee('SPEC-003');

        $this->actingAs($user)
            ->get(route('computers.index', ['spec_category' => 'Storage']))
            ->assertOk()
            ->assertDontSee('SPEC-001')
            ->assertDontSee('SPEC-002')
            ->assertSee('Belum ada data komputer');
    }

    public function test_category_and_keyword_must_match_the_same_component(): void
    {
        $user = $this->userWith(['view-computers'], 'viewer-kombinasi@test');

        // Processor + i5: cocok pada komponen yang sama.
        $this->actingAs($user)
            ->get(route('computers.index', ['spec_category' => 'Processor', 'spec' => 'i5']))
            ->assertOk()
            ->assertSee('SPEC-001')
            ->assertDontSee('SPEC-002');

        // Processor + 8GB: 8GB ada di RAM, bukan di Processor — tidak boleh cocok
        // walaupun komputer yang sama punya keduanya.
        $this->actingAs($user)
            ->get(route('computers.index', ['spec_category' => 'Processor', 'spec' => '8GB']))
            ->assertOk()
            ->assertDontSee('SPEC-001')
            ->assertDontSee('SPEC-002')
            ->assertSee('Belum ada data komputer');
    }

    public function test_spec_filter_combines_with_laboratory_filter(): void
    {
        $user = $this->userWith(['view-computers'], 'viewer-lab-spec@test');

        $this->actingAs($user)
            ->get(route('computers.index', ['laboratory_id' => $this->labA->id, 'spec' => 'i5']))
            ->assertOk()
            ->assertSee('SPEC-001')
            ->assertDontSee('SPEC-002');

        // Lab B tidak punya hardware i5 — hasil harus kosong, bukan mengabaikan lab.
        $this->actingAs($user)
            ->get(route('computers.index', ['laboratory_id' => $this->labB->id, 'spec' => 'i5']))
            ->assertOk()
            ->assertDontSee('SPEC-001')
            ->assertDontSee('SPEC-002')
            ->assertSee('Belum ada data komputer');
    }

    public function test_search_does_not_bypass_laboratory_filter(): void
    {
        $user = $this->userWith(['view-computers'], 'viewer-regresi@test');

        // Kata kunci = nama lab B, tapi filter lab = lab A.
        // Dulu bug orWhere membuat SPEC-002 (lab B) ikut muncul.
        $this->actingAs($user)
            ->get(route('computers.index', ['laboratory_id' => $this->labA->id, 'search' => 'Lab Jaringan']))
            ->assertOk()
            ->assertDontSee('SPEC-002')
            ->assertSee('Belum ada data komputer');

        // Filter lab + kode tetap berfungsi normal.
        $this->actingAs($user)
            ->get(route('computers.index', ['laboratory_id' => $this->labA->id, 'search' => 'SPEC-001']))
            ->assertOk()
            ->assertSee('SPEC-001')
            ->assertDontSee('SPEC-002');
    }

    public function test_export_follows_spec_filter(): void
    {
        $user = $this->userWith(['view-computers', 'export-computers'], 'exporter-spec@test');

        $rows = $this->rows($this->actingAs($user)->get(route('computers.export', ['spec' => 'ryzen'])));
        $this->assertCount(2, $rows);
        $this->assertSame('SPEC-002', $rows[1][0]);

        $rows = $this->rows($this->actingAs($user)->get(route('computers.spec-export', ['spec_category' => 'RAM'])));
        $this->assertCount(2, $rows);
        $this->assertSame('SPEC-001', $rows[1][0]);
    }

    public function test_index_shows_all_hardware_chips_without_overflow_counter(): void
    {
        $user = $this->userWith(['view-computers'], 'viewer-hw@test');

        foreach (range(1, 4) as $i) {
            $hw = Hardware::create(['name' => "Komponen Tambahan {$i}", 'code' => "HW-SP-T{$i}", 'category' => 'Lainnya']);
            $this->pcNoSpec->hardware()->attach($hw->id);
        }

        $response = $this->actingAs($user)->get(route('computers.index', ['search' => 'SPEC-003']));
        $response->assertOk()->assertSee('SPEC-003');

        // Semua hardware tampil — tidak ada lagi chip yang dipotong +N.
        foreach ($this->pcNoSpec->hardware as $hw) {
            $response->assertSee($hw->name);
        }
        $response->assertDontSee('>+</', false);
    }

    public function test_index_renders_spec_filter_ui_and_reset_link(): void
    {
        $user = $this->userWith(['view-computers'], 'viewer-ui@test');

        $this->actingAs($user)
            ->get(route('computers.index'))
            ->assertOk()
            ->assertSee('name="spec_category"', false)
            ->assertSee('name="spec"', false)
            ->assertSee('Semua Spesifikasi')
            ->assertSee('value="Processor"', false)
            ->assertDontSee('Reset');

        // Kategori ekstra di luar Hardware::CATEGORIES tetap muncul di dropdown.
        $extra = Hardware::create(['name' => 'Speaker Aktif', 'code' => 'HW-SP-EX', 'category' => 'Speaker']);
        $this->pcNoSpec->hardware()->attach($extra->id);

        $this->actingAs($user)
            ->get(route('computers.index', ['spec' => 'i5']))
            ->assertOk()
            ->assertSee('value="Speaker"', false)
            ->assertSee('value="i5"', false)
            ->assertSee('Reset');
    }
}
