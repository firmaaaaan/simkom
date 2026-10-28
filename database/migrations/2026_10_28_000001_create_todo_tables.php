<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('todo_columns', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->string('name', 100);
            $table->unsignedTinyInteger('position')->default(0);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->index(['user_id', 'position']);
        });

        Schema::create('todo_labels', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->string('name', 50);
            $table->string('color', 7)->default('#6b7280');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->index('user_id');
        });

        Schema::create('todo_tasks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('todo_column_id');
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('due_date')->nullable();
            $table->enum('priority', ['low', 'medium', 'high'])->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('todo_column_id')->references('id')->on('todo_columns')->cascadeOnDelete();
            $table->index(['todo_column_id', 'position']);
            $table->index('due_date');
        });

        Schema::create('todo_task_labels', function (Blueprint $table) {
            $table->uuid('todo_task_id');
            $table->uuid('todo_label_id');

            $table->foreign('todo_task_id')->references('id')->on('todo_tasks')->cascadeOnDelete();
            $table->foreign('todo_label_id')->references('id')->on('todo_labels')->cascadeOnDelete();
            $table->primary(['todo_task_id', 'todo_label_id']);
        });

        Schema::create('todo_checklist_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('todo_task_id');
            $table->string('text');
            $table->boolean('is_done')->default(false);
            $table->unsignedTinyInteger('position')->default(0);
            $table->timestamps();

            $table->foreign('todo_task_id')->references('id')->on('todo_tasks')->cascadeOnDelete();
            $table->index(['todo_task_id', 'position']);
        });

        Schema::create('todo_attachments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('todo_task_id');
            $table->string('original_name');
            $table->string('stored_name');
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('size')->default(0);
            $table->timestamps();

            $table->foreign('todo_task_id')->references('id')->on('todo_tasks')->cascadeOnDelete();
            $table->index('todo_task_id');
        });

        Schema::create('todo_comments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('todo_task_id');
            $table->uuid('user_id');
            $table->text('body');
            $table->timestamps();

            $table->foreign('todo_task_id')->references('id')->on('todo_tasks')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->index('todo_task_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('todo_comments');
        Schema::dropIfExists('todo_attachments');
        Schema::dropIfExists('todo_checklist_items');
        Schema::dropIfExists('todo_task_labels');
        Schema::dropIfExists('todo_tasks');
        Schema::dropIfExists('todo_labels');
        Schema::dropIfExists('todo_columns');
    }
};
