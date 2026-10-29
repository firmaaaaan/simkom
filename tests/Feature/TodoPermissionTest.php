<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\TodoColumn;
use App\Models\TodoTask;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TodoPermissionTest extends TestCase
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
            ['name' => 'User Todo Permission', 'password' => 'rahasia123']
        );
        $user->roles()->syncWithoutDetaching([$role->id]);

        return $user;
    }

    private function column(User $user): TodoColumn
    {
        return TodoColumn::create([
            'user_id' => $user->id,
            'name' => 'To Do',
            'position' => 0,
        ]);
    }

    private function task(User $user, TodoColumn $column): TodoTask
    {
        return TodoTask::create([
            'user_id' => $user->id,
            'todo_column_id' => $column->id,
            'title' => 'Kartu Uji',
            'position' => 0,
        ]);
    }

    public function test_view_routes_are_forbidden_without_permission(): void
    {
        $user = $this->userWith([], 'tanpa-view-todos@uji.test');

        $this->actingAs($user)->get(route('todos.index'))->assertForbidden();
        $this->actingAs($user)->getJson(route('todos.index'))->assertForbidden();
    }

    public function test_create_routes_are_forbidden_without_permission(): void
    {
        $user = $this->userWith([], 'tanpa-create-todos@uji.test');
        $column = $this->column($user);
        $task = $this->task($user, $column);

        $this->actingAs($user)
            ->postJson(route('todos.quick-add'), ['column_id' => $column->id, 'title' => 'X'])
            ->assertForbidden();
        $this->actingAs($user)
            ->postJson(route('todos.tasks.comments.store', $task), ['body' => 'Y'])
            ->assertForbidden();
    }

    public function test_edit_routes_are_forbidden_without_permission(): void
    {
        $user = $this->userWith([], 'tanpa-edit-todos@uji.test');
        $column = $this->column($user);
        $task = $this->task($user, $column);

        $this->actingAs($user)
            ->putJson(route('todos.tasks.update', $task), ['title' => 'Hack', 'column_id' => $column->id])
            ->assertForbidden();
        $this->actingAs($user)
            ->patchJson(route('todos.tasks.move', $task), ['column_id' => $column->id, 'position' => 0])
            ->assertForbidden();
    }

    public function test_delete_routes_are_forbidden_without_permission(): void
    {
        $user = $this->userWith([], 'tanpa-delete-todos@uji.test');
        $column = $this->column($user);
        $task = $this->task($user, $column);

        $this->actingAs($user)
            ->deleteJson(route('todos.tasks.destroy', $task))
            ->assertForbidden();
        $this->actingAs($user)
            ->deleteJson(route('todos.columns.destroy', $column->id))
            ->assertForbidden();
    }

    public function test_view_only_user_can_see_index_but_not_mutate(): void
    {
        $user = $this->userWith(['view-todos'], 'viewer-todos@uji.test');
        $column = $this->column($user);
        $task = $this->task($user, $column);

        $this->actingAs($user)->get(route('todos.index'))->assertOk();

        $this->actingAs($user)
            ->postJson(route('todos.quick-add'), ['column_id' => $column->id, 'title' => 'X'])
            ->assertForbidden();
        $this->actingAs($user)
            ->putJson(route('todos.tasks.update', $task), ['title' => 'Hack', 'column_id' => $column->id])
            ->assertForbidden();
        $this->actingAs($user)
            ->deleteJson(route('todos.tasks.destroy', $task))
            ->assertForbidden();
    }

    public function test_guest_is_redirected_even_without_permission(): void
    {
        $this->get(route('todos.index'))->assertRedirect(route('login'));
    }
}
