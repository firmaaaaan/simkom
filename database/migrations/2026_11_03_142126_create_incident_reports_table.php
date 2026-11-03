<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incident_reports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('laboratory_id')->nullable()->constrained()->nullOnDelete();
            $table->date('incident_date');
            $table->string('reporter_name')->nullable();
            $table->string('incident_type')->nullable();
            $table->text('description')->nullable();
            $table->text('impact')->nullable();
            $table->text('immediate_action')->nullable();
            $table->text('follow_up')->nullable();
            $table->string('status')->default('Open');
            $table->string('attachment_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incident_reports');
    }
};