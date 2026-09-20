<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('computer_borrowings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('tracking_code', 20)->nullable()->unique();
            $table->uuid('computer_id');
            $table->uuid('laboratory_id');
            $table->string('borrower_name');
            $table->string('borrower_prodi')->nullable();
            $table->string('borrower_nim')->nullable();
            $table->string('purpose');
            $table->string('borrow_date');
            $table->string('borrow_time_start');
            $table->string('borrow_time_end');
            $table->enum('status', ['Pending', 'Approved', 'Rejected', 'Returned'])->default('Pending');
            $table->text('admin_notes')->nullable();
            $table->timestamps();
            $table->foreign('computer_id')->references('id')->on('computers')->cascadeOnDelete();
            $table->foreign('laboratory_id')->references('id')->on('laboratories')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('computer_borrowings');
    }
};
