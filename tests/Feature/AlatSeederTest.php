<?php
namespace Tests\Feature;
use App\Models\Alat;
use App\Models\Laboratory;
use Database\Seeders\AlatSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AlatSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_creates_alat_grouped_by_room(): void
    {
        $this->seed(AlatSeeder::class);

        $this->assertSame(34, Alat::count());
        $this->assertSame(6, Laboratory::count());
        $this->assertSame(6, Alat::whereNotNull('laboratory_id')->distinct()->count('laboratory_id'));

        $this->assertDatabaseHas('alat', ['code' => 'INV.ROB.002', 'name' => 'Oscilloscope', 'merk_type' => 'Rigol DS1054Z']);
        $this->assertDatabaseHas('alat', ['code' => 'INV.NET.005', 'status' => 'Perlu Pemeliharaan']);
    }

    public function test_seeder_is_idempotent(): void
    {
        $this->seed(AlatSeeder::class);
        $this->seed(AlatSeeder::class);

        $this->assertSame(34, Alat::count());
        $this->assertSame(6, Laboratory::count());
    }

    public function test_seeder_reuses_existing_laboratory(): void
    {
        $lab = Laboratory::create(['name' => 'Lab Komputer 1', 'code' => 'LAB-KOM-01', 'location' => 'Gedung A Lantai 2', 'capacity' => 40]);

        $this->seed(AlatSeeder::class);

        $this->assertSame(6, Laboratory::count());
        $this->assertSame($lab->id, Alat::where('code', 'INV.KOM.001')->firstOrFail()->laboratory_id);
    }
}
