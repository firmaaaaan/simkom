<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lab_layouts', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->foreignUuid('laboratory_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->json('layout_data')->nullable();
            $t->unsignedInteger('grid_cols')->default(12);
            $t->unsignedInteger('grid_rows')->default(8);
            $t->unsignedInteger('cell_size')->default(100);
            $t->string('background_color', 7)->default('#ffffff');
            $t->boolean('is_published')->default(false);
            $t->boolean('is_draft')->default(true);
            $t->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
            $t->index(['laboratory_id', 'is_published']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lab_layouts');
    }
};