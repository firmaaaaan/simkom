<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('software_licenses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('software_id');
            $table->string('label')->nullable();
            $table->string('license_key')->nullable();
            $table->uuid('assigned_computer_id')->nullable();
            $table->date('purchase_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('software_id');
            $table->index('assigned_computer_id');
            $table->unique(['software_id', 'assigned_computer_id']);

            $table->foreign('software_id')->references('id')->on('software')->cascadeOnDelete();
            $table->foreign('assigned_computer_id')->references('id')->on('computers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('software_licenses');
    }
};
