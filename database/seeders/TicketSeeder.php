<?php

namespace Database\Seeders;

use App\Models\Ticket;
use App\Models\TicketComment;
use App\Models\Laboratory;
use App\Models\Computer;
use App\Models\AcademicYear;
use App\Models\User;
use Illuminate\Database\Seeder;

class TicketSeeder extends Seeder
{
    public function run(): void
    {
        $labs = Laboratory::all();
        $academicYear = AcademicYear::where('status', 'Aktif')->first();
        $users = User::all();

        if ($labs->isEmpty() || !$academicYear || $users->isEmpty()) {
            return;
        }

        $tickets = [
            [
                'title' => 'Mouse tidak berfungsi di PC Lab-01',
                'description' => 'Mouse pada komputer dengan kode LAB-01-003 tidak merespons saat digunakan. Sudah dicoba dipindahkan ke port USB lain tetapi tetap tidak berfungsi.',
                'category' => 'Hardware',
                'priority' => 'Sedang',
                'status' => 'Open',
            ],
            [
                'title' => 'Windows tidak bisa login',
                'description' => 'Komputer LAB-02-001 stuck pada layar login. Setelah memasukkan password, layar berputar terus menerus tidak masuk ke desktop.',
                'category' => 'Software',
                'priority' => 'Tinggi',
                'status' => 'In Progress',
            ],
            [
                'title' => 'Monitor berkedip-kedip',
                'description' => 'Monitor pada PC LAB-03-005 berkedip-kedip terus menerus. Sudah dicoba mengganti kabel VGA tetapi masalah masih terjadi.',
                'category' => 'Hardware',
                'priority' => 'Tinggi',
                'status' => 'Open',
            ],
            [
                'title' => 'Tidak bisa koneksi internet',
                'description' => 'Beberapa komputer di LAB-01 tidak bisa terkoneksi ke internet. Kemungkinan masalah pada kabel jaringan atau switch.',
                'category' => 'Jaringan',
                'priority' => 'Darurat',
                'status' => 'Open',
            ],
            [
                'title' => 'Printer tidak terdeteksi',
                'description' => 'Printer HP LaserJet di ruang Lab-02 tidak terdeteksi oleh komputer. Sudah dicoba restart printer dan reinstall driver.',
                'category' => 'Hardware',
                'priority' => 'Sedang',
                'status' => 'Resolved',
            ],
            [
                'title' => 'Blue Screen saat buka aplikasi berat',
                'description' => 'Komputer LAB-03-002 mengalami BSOD (Blue Screen of Death) saat menjalankan aplikasi AutoCAD. RAM mungkin kurang.',
                'category' => 'Komputer',
                'priority' => 'Tinggi',
                'status' => 'In Progress',
            ],
            [
                'title' => 'Keyboard beberapa tombol rusak',
                'description' => 'Tombol A, S, dan D pada keyboard di LAB-01-007 tidak berfungsi. Perlu penggantian keyboard.',
                'category' => 'Hardware',
                'priority' => 'Rendah',
                'status' => 'Closed',
            ],
            [
                'title' => 'Listrik padam saat praktikum',
                'description' => 'Terjadi pemadaman listrik di LAB-02 pada saat jam praktikum berlangsung. UPS beberapa unit sudah mulai lemah.',
                'category' => 'Listrik/UPS',
                'priority' => 'Darurat',
                'status' => 'Resolved',
            ],
            [
                'title' => 'MS Office tidak terinstall',
                'description' => 'Beberapa komputer di LAB-01 belum terinstall Microsoft Office. Mahasiswa tidak bisa mengerjakan tugas.',
                'category' => 'Software',
                'priority' => 'Sedang',
                'status' => 'Open',
            ],
            [
                'title' => 'Hard disk error pada PC LAB-03',
                'description' => 'Komputer LAB-03-004 menampilkan pesan "Hard disk error" saat booting. Data praktikum mahasiswa tersimpan di hard disk tersebut.',
                'category' => 'Komputer',
                'priority' => 'Darurat',
                'status' => 'In Progress',
            ],
        ];

        foreach ($tickets as $index => $data) {
            $lab = $labs->random();
            $computer = Computer::where('laboratory_id', $lab->id)->first();
            $reporter = $users->random();
            $assignee = $users->where('id', '!=', $reporter->id)->random();

            $ticket = Ticket::create([
                'laboratory_id' => $lab->id,
                'computer_id' => $computer?->id,
                'academic_year_id' => $academicYear->id,
                'reported_by' => $reporter->id,
                'assigned_to' => $assignee->id,
                'category' => $data['category'],
                'title' => $data['title'],
                'description' => $data['description'],
                'priority' => $data['priority'],
                'status' => $data['status'],
            ]);

            // Tambah komentar untuk beberapa tiket
            if ($index < 5) {
                $commentUser = $users->random();
                TicketComment::create([
                    'ticket_id' => $ticket->id,
                    'user_id' => $commentUser->id,
                    'message' => $this->getComment($index),
                ]);
            }
        }
    }

    private function getComment(int $index): string
    {
        $comments = [
            'Sudah saya cek, memang perlu diganti. Saya pesan barangnya dulu.',
            'Driver sudah diinstall ulang, silakan dicoba lagi.',
            'Teknisi sedang dalam perjalanan menuju lab.',
            'Sudah diperbaiki, silakan cek kembali.',
            'Menunggu konfirmasi dari vendor untuk penggantian barang.',
        ];

        return $comments[$index] ?? 'Sedang diproses.';
    }
}
