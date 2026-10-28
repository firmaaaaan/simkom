<?php

namespace Tests\Feature;

use App\Models\TodoColumn;
use App\Models\TodoTask;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TodoReminderTest extends TestCase
{
    use RefreshDatabase;

    private function configureTelegram(): void
    {
        config()->set('telegram.bot_token', 'test-token');
        config()->set('telegram.chat_id', '1166966295');
        config()->set('telegram.retry_delay_ms', 0);
    }

    private function user(string $name = 'Budi', string $email = 'todo-remind@uji.test'): User
    {
        return User::firstOrCreate(
            ['email' => $email],
            ['name' => $name, 'password' => 'rahasia123']
        );
    }

    private function column(User $user, string $name = 'To Do', int $position = 0): TodoColumn
    {
        return TodoColumn::create([
            'user_id' => $user->id,
            'name' => $name,
            'position' => $position,
        ]);
    }

    private function task(User $user, TodoColumn $column, string $title, string $dueDate): TodoTask
    {
        return TodoTask::create([
            'user_id' => $user->id,
            'todo_column_id' => $column->id,
            'title' => $title,
            'due_date' => $dueDate,
        ]);
    }

    public function test_task_due_tomorrow_is_reminded(): void
    {
        $this->configureTelegram();
        Http::fake();
        $user = $this->user();
        $column = $this->column($user);
        $this->task($user, $column, 'Beli kabel LAN', Carbon::tomorrow()->toDateString());

        $this->artisan('todo:remind-due')->assertSuccessful();

        Http::assertSent(function (Request $request) {
            return str_contains($request->url(), '/bottest-token/sendMessage')
                && $request['chat_id'] === '1166966295'
                && str_contains($request['text'], 'Beli kabel LAN')
                && str_contains($request['text'], 'Budi')
                && str_contains($request['text'], 'Jatuh tempo besok (H-1)')
                && str_contains($request['text'], '<b>Pengingat To-Do List</b>')
                && str_contains($request['text'], '<a href="'.e(route('todos.index')).'">Lihat detail</a>');
        });
    }

    public function test_overdue_task_is_grouped_in_terlewat_section(): void
    {
        $this->configureTelegram();
        Http::fake();
        $user = $this->user();
        $column = $this->column($user);
        $this->task($user, $column, 'Laporan terlambat', Carbon::yesterday()->toDateString());

        $this->artisan('todo:remind-due')->assertSuccessful();

        Http::assertSent(fn (Request $request) => str_contains($request['text'], '⏰ Terlewat:')
            && str_contains($request['text'], 'Laporan terlambat'));
    }

    public function test_task_due_today_is_grouped_in_hari_ini_section(): void
    {
        $this->configureTelegram();
        Http::fake();
        $user = $this->user();
        $column = $this->column($user);
        $this->task($user, $column, 'Tugas hari ini', Carbon::today()->toDateString());

        $this->artisan('todo:remind-due')->assertSuccessful();

        Http::assertSent(fn (Request $request) => str_contains($request['text'], 'Jatuh tempo hari ini:')
            && str_contains($request['text'], 'Tugas hari ini'));
    }

    public function test_tasks_in_done_named_columns_are_excluded(): void
    {
        $this->configureTelegram();
        Http::fake();
        $user = $this->user();
        $tomorrow = Carbon::tomorrow()->toDateString();
        $this->task($user, $this->column($user, 'Done', 0), 'Task di Done', $tomorrow);
        $this->task($user, $this->column($user, 'Selesai', 1), 'Task di Selesai', $tomorrow);
        $this->task($user, $this->column($user, ' selesai ', 2), 'Task di Selesai Spasi', $tomorrow);

        $this->artisan('todo:remind-due')->assertSuccessful();

        Http::assertNothingSent();
    }

    public function test_task_due_beyond_tomorrow_is_excluded(): void
    {
        $this->configureTelegram();
        Http::fake();
        $user = $this->user();
        $column = $this->column($user);
        $this->task($user, $column, 'Task masa depan', Carbon::today()->addDays(3)->toDateString());

        $this->artisan('todo:remind-due')->assertSuccessful();

        Http::assertNothingSent();
    }

    public function test_task_without_due_date_is_excluded(): void
    {
        $this->configureTelegram();
        Http::fake();
        $user = $this->user();
        $column = $this->column($user);
        $task = $this->task($user, $column, 'Task tanpa tenggat', Carbon::tomorrow()->toDateString());
        $task->update(['due_date' => null]);

        $this->artisan('todo:remind-due')->assertSuccessful();

        Http::assertNothingSent();
    }

    public function test_no_matching_tasks_sends_nothing(): void
    {
        $this->configureTelegram();
        Http::fake();
        $user = $this->user();
        $this->column($user);

        $this->artisan('todo:remind-due')->assertSuccessful();

        Http::assertNothingSent();
    }

    public function test_multiple_owners_are_aggregated_in_one_message(): void
    {
        $this->configureTelegram();
        Http::fake();
        $budi = $this->user('Budi', 'budi-remind@uji.test');
        $ani = $this->user('Ani', 'ani-remind@uji.test');
        $tomorrow = Carbon::tomorrow()->toDateString();
        $this->task($budi, $this->column($budi), 'Kerjaan Budi', $tomorrow);
        $this->task($ani, $this->column($ani), 'Kerjaan Ani', $tomorrow);

        $this->artisan('todo:remind-due')->assertSuccessful();

        Http::assertSentCount(1);
        Http::assertSent(fn (Request $request) => str_contains($request['text'], 'Kerjaan Budi')
            && str_contains($request['text'], 'Kerjaan Ani')
            && str_contains($request['text'], '2 task belum selesai'));
    }

    public function test_command_succeeds_without_telegram_config(): void
    {
        config()->set('telegram.bot_token', '');
        config()->set('telegram.chat_id', '');
        Http::fake();
        $user = $this->user();
        $column = $this->column($user);
        $this->task($user, $column, 'Task uji', Carbon::tomorrow()->toDateString());

        $this->artisan('todo:remind-due')->assertSuccessful();

        Http::assertNothingSent();
    }
}
