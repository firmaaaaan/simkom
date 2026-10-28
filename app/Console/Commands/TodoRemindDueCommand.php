<?php

namespace App\Console\Commands;

use App\Models\TodoColumn;
use App\Models\TodoTask;
use App\Services\TelegramNotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class TodoRemindDueCommand extends Command
{
    protected $signature = 'todo:remind-due';

    protected $description = 'Kirim pengingat Telegram untuk To-Do List jatuh tempo H-1, hari ini, dan yang terlewat';

    public function handle(TelegramNotificationService $telegram): int
    {
        $today = Carbon::today();
        $tomorrow = $today->copy()->addDay();

        $tasks = TodoTask::query()
            ->whereNotNull('due_date')
            ->where('due_date', '<=', $tomorrow)
            ->whereHas('column', fn ($query) => $query
                ->whereRaw('LOWER(TRIM(name)) NOT IN (?, ?)', TodoColumn::DONE_NAMES))
            ->with(['user:id,name', 'column:id,name'])
            ->orderBy('due_date')
            ->get();

        if ($tasks->isEmpty()) {
            $this->info('Tidak ada To-Do List yang perlu diingatkan.');

            return self::SUCCESS;
        }

        $overdue = $tasks->filter(fn (TodoTask $task) => $task->due_date->lt($today));
        $dueToday = $tasks->filter(fn (TodoTask $task) => $task->due_date->isSameDay($today));
        $dueTomorrow = $tasks->filter(fn (TodoTask $task) => $task->due_date->isSameDay($tomorrow));

        $lines = [count($tasks).' task belum selesai per '.$today->translatedFormat('d M Y').'.'];

        $sections = [
            ['⏰ Terlewat:', $overdue],
            ['📅 Jatuh tempo hari ini:', $dueToday],
            ['📅 Jatuh tempo besok (H-1):', $dueTomorrow],
        ];

        foreach ($sections as [$header, $group]) {
            if ($group->isEmpty()) {
                continue;
            }

            $lines[] = '';
            $lines[] = $header;
            foreach ($group as $task) {
                $lines[] = '• '.$this->describe($task);
            }
        }

        $chunks = $this->chunkLines($lines, 3900);

        foreach ($chunks as $chunk) {
            $telegram->send('Pengingat To-Do List', $chunk, route('todos.index'));
        }

        $this->info('Pengingat To-Do List terkirim: '.count($chunks).' pesan, '.count($tasks).' task.');

        return self::SUCCESS;
    }

    private function describe(TodoTask $task): string
    {
        return sprintf(
            '%s — jatuh tempo %s (%s)',
            $task->title,
            $task->due_date->translatedFormat('d M Y'),
            $task->user->name ?? '-'
        );
    }

    /**
     * Pecah daftar baris menjadi beberapa pesan agar tidak melebihi
     * batas 4096 karakter milik Telegram.
     */
    private function chunkLines(array $lines, int $limit): array
    {
        $chunks = [];
        $current = '';

        foreach ($lines as $line) {
            if ($current !== '' && mb_strlen($current) + mb_strlen($line) + 1 > $limit) {
                $chunks[] = $current;
                $current = $line;

                continue;
            }

            $current .= ($current === '' ? '' : "\n").$line;
        }

        if ($current !== '') {
            $chunks[] = $current;
        }

        return $chunks;
    }
}
