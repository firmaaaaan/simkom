<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\TodoColumn;
use App\Models\TodoTask;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TodoKanbanTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $email): User
    {
        $role = Role::firstOrCreate(['name' => 'role-'.md5($email)], ['label' => 'Role Uji Todo']);
        foreach (['view-todos', 'create-todos', 'edit-todos', 'delete-todos'] as $name) {
            $permission = Permission::firstOrCreate(['name' => $name], ['label' => $name]);
            $role->permissions()->syncWithoutDetaching([$permission->id]);
        }

        $user = User::firstOrCreate(
            ['email' => $email],
            ['name' => 'User Todo', 'password' => 'rahasia123']
        );
        $user->roles()->syncWithoutDetaching([$role->id]);

        return $user;
    }

    private function column(User $user, string $name = 'To Do', int $position = 0): TodoColumn
    {
        return TodoColumn::create([
            'user_id' => $user->id,
            'name' => $name,
            'position' => $position,
        ]);
    }

    private function task(User $user, TodoColumn $column, string $title = 'Kartu Uji', int $position = 0): TodoTask
    {
        return TodoTask::create([
            'user_id' => $user->id,
            'todo_column_id' => $column->id,
            'title' => $title,
            'position' => $position,
        ]);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('todos.index'))->assertRedirect(route('login'));
    }

    public function test_index_creates_default_columns_for_new_user(): void
    {
        $user = $this->user('todo-first@uji.test');

        $this->actingAs($user)->get(route('todos.index'))->assertOk();

        $columns = TodoColumn::where('user_id', $user->id)->orderBy('position')->pluck('name')->all();
        $this->assertSame(['To Do', 'Doing', 'Done'], $columns);
    }

    public function test_quick_add_creates_task_in_column(): void
    {
        $user = $this->user('todo-quick@uji.test');
        $column = $this->column($user);

        $this->actingAs($user)
            ->postJson(route('todos.quick-add'), ['column_id' => $column->id, 'title' => 'Beli peralatan'])
            ->assertCreated()
            ->assertJsonPath('task.title', 'Beli peralatan')
            ->assertJsonPath('task.column_id', $column->id);

        $this->assertDatabaseHas('todo_tasks', ['title' => 'Beli peralatan', 'user_id' => $user->id]);
    }

    public function test_update_task_moves_between_columns(): void
    {
        $user = $this->user('todo-move@uji.test');
        $todo = $this->column($user, 'To Do', 0);
        $done = $this->column($user, 'Done', 1);
        $task = $this->task($user, $todo);

        $this->actingAs($user)
            ->putJson(route('todos.tasks.update', $task), [
                'title' => $task->title,
                'column_id' => $done->id,
                'due_date' => '2026-11-01',
                'priority' => 'high',
            ])
            ->assertOk();

        $task->refresh();
        $this->assertSame($done->id, $task->todo_column_id);
        $this->assertSame('high', $task->priority);
        $this->assertSame('2026-11-01', $task->due_date->toDateString());
    }

    public function test_move_endpoint_reorders_positions(): void
    {
        $user = $this->user('todo-reorder@uji.test');
        $a = $this->column($user, 'A', 0);
        $b = $this->column($user, 'B', 1);
        $t1 = $this->task($user, $a, 'Satu', 0);
        $t2 = $this->task($user, $a, 'Dua', 1);

        $this->actingAs($user)
            ->patchJson(route('todos.tasks.move', $t2), ['column_id' => $a->id, 'position' => 0])
            ->assertOk();

        $this->assertSame(0, $t2->refresh()->position);
        $this->assertSame(1, $t1->refresh()->position);

        $this->actingAs($user)
            ->patchJson(route('todos.tasks.move', $t1), ['column_id' => $b->id, 'position' => 0])
            ->assertOk();

        $this->assertSame($b->id, $t1->refresh()->todo_column_id);
    }

    public function test_user_cannot_access_other_users_task(): void
    {
        $owner = $this->user('todo-owner@uji.test');
        $intruder = $this->user('todo-intruder@uji.test');
        $column = $this->column($owner);
        $task = $this->task($owner, $column);

        $this->actingAs($intruder)->getJson(route('todos.tasks.detail', $task))->assertNotFound();
        $this->actingAs($intruder)->putJson(route('todos.tasks.update', $task), [
            'title' => 'Hack',
            'column_id' => $column->id,
        ])->assertNotFound();
        $this->actingAs($intruder)->deleteJson(route('todos.tasks.destroy', $task))->assertNotFound();
    }

    public function test_checklist_lifecycle(): void
    {
        $user = $this->user('todo-check@uji.test');
        $column = $this->column($user);
        $task = $this->task($user, $column);

        $response = $this->actingAs($user)
            ->postJson(route('todos.tasks.checklist.store', $task), ['text' => 'Pasang OS'])
            ->assertCreated()
            ->assertJsonPath('checklist_total', 1);
        $itemId = $response->json('item.id');

        $this->actingAs($user)
            ->patchJson(route('todos.checklist.toggle', $itemId), ['is_done' => true])
            ->assertOk()
            ->assertJsonPath('item.is_done', true)
            ->assertJsonPath('checklist_done', 1);

        $this->actingAs($user)
            ->deleteJson(route('todos.checklist.destroy', $itemId))
            ->assertOk()
            ->assertJsonPath('checklist_total', 0);
    }

    public function test_attachment_upload_and_download(): void
    {
        Storage::fake('local');
        $user = $this->user('todo-file@uji.test');
        $column = $this->column($user);
        $task = $this->task($user, $column);
        $file = UploadedFile::fake()->create('catatan.pdf', 100, 'application/pdf');

        $response = $this->actingAs($user)
            ->postJson(route('todos.tasks.attachments.store', $task), ['file' => $file])
            ->assertCreated()
            ->assertJsonPath('attachment.original_name', 'catatan.pdf');

        $attachmentId = $response->json('attachment.id');

        $this->actingAs($user)
            ->get(route('todos.attachments.download', $attachmentId))
            ->assertOk()
            ->assertDownload('catatan.pdf');
    }

    public function test_attachment_rejects_oversized_file(): void
    {
        Storage::fake('local');
        $user = $this->user('todo-bigfile@uji.test');
        $column = $this->column($user);
        $task = $this->task($user, $column);
        $file = UploadedFile::fake()->create('besar.pdf', 6000, 'application/pdf');

        $this->actingAs($user)
            ->postJson(route('todos.tasks.attachments.store', $task), ['file' => $file])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['file']);
    }

    public function test_comments_lifecycle(): void
    {
        $user = $this->user('todo-comment@uji.test');
        $column = $this->column($user);
        $task = $this->task($user, $column);

        $response = $this->actingAs($user)
            ->postJson(route('todos.tasks.comments.store', $task), ['body' => 'Sudah selesai?'])
            ->assertCreated()
            ->assertJsonPath('comment_count', 1);
        $commentId = $response->json('comment.id');

        $this->actingAs($user)
            ->deleteJson(route('todos.comments.destroy', $commentId))
            ->assertOk()
            ->assertJsonPath('comment_count', 0);
    }

    public function test_comment_of_other_user_cannot_be_deleted(): void
    {
        $owner = $this->user('todo-cowner@uji.test');
        $other = $this->user('todo-cother@uji.test');
        $column = $this->column($owner);
        $task = $this->task($owner, $column);

        $commentId = $this->actingAs($owner)
            ->postJson(route('todos.tasks.comments.store', $task), ['body' => 'Komentar pemilik'])
            ->json('comment.id');

        $this->actingAs($other)
            ->deleteJson(route('todos.comments.destroy', $commentId))
            ->assertNotFound();
    }

    public function test_label_crud_and_assign_to_task(): void
    {
        $user = $this->user('todo-label@uji.test');
        $column = $this->column($user);
        $task = $this->task($user, $column);

        $response = $this->actingAs($user)
            ->postJson(route('todos.labels.store'), ['name' => 'Urgent', 'color' => '#ef4444'])
            ->assertCreated()
            ->assertJsonPath('label.name', 'Urgent');
        $labelId = $response->json('label.id');

        $this->actingAs($user)
            ->patchJson(route('todos.tasks.labels', $task), ['label_ids' => [$labelId]])
            ->assertOk();

        $this->assertTrue($task->labels()->where('todo_label_id', $labelId)->exists());

        $this->actingAs($user)
            ->putJson(route('todos.labels.update', $labelId), ['name' => 'Penting', 'color' => '#f59e0b'])
            ->assertOk()
            ->assertJsonPath('label.name', 'Penting');

        $this->actingAs($user)
            ->deleteJson(route('todos.labels.destroy', $labelId))
            ->assertOk();
        $this->assertDatabaseMissing('todo_labels', ['id' => $labelId]);
    }

    public function test_delete_column_moves_tasks_to_first_remaining_column(): void
    {
        $user = $this->user('todo-delcol@uji.test');
        $first = $this->column($user, 'To Do', 0);
        $second = $this->column($user, 'Doing', 1);
        $task = $this->task($user, $second);

        $this->actingAs($user)
            ->deleteJson(route('todos.columns.destroy', $second->id))
            ->assertOk()
            ->assertJsonPath('target_column_id', $first->id);

        $this->assertSame($first->id, $task->refresh()->todo_column_id);
        $this->assertDatabaseMissing('todo_columns', ['id' => $second->id]);
    }

    public function test_column_reorder_updates_positions(): void
    {
        $user = $this->user('todo-colreorder@uji.test');
        $a = $this->column($user, 'A', 0);
        $b = $this->column($user, 'B', 1);

        $this->actingAs($user)
            ->patchJson(route('todos.columns.reorder'), ['columns' => [$b->id, $a->id]])
            ->assertOk();

        $this->assertSame(0, $b->refresh()->position);
        $this->assertSame(1, $a->refresh()->position);
    }

    public function test_index_shows_own_board(): void
    {
        $user = $this->user('todo-board@uji.test');
        $column = $this->column($user);
        $this->task($user, $column, 'Tugas Milik Saya');

        $this->actingAs($user)
            ->get(route('todos.index'))
            ->assertOk()
            ->assertSee('Tugas Milik Saya')
            ->assertSee('Kelola Kolom');
    }
}
