<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lab_usages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('laboratory_id');
            $table->string('user_name');
            $table->string('user_prodi');
            $table->string('purpose');
            $table->string('day');
            $table->enum('status', ['In', 'Out'])->default('In');
            $table->timestamp('checked_in_at');
            $table->timestamp('validated_at')->nullable();
            $table->uuid('validated_by')->nullable();
            $table->string('exit_note')->nullable();
            $table->timestamps();
            $table->foreign('laboratory_id')->references('id')->on('laboratories')->cascadeOnDelete();
            $table->foreign('validated_by')->references('id')->on('users')->nullOnDelete();
            $table->index(['laboratory_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lab_usages');
    }
};
