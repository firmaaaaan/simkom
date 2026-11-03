<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('incident_reports', function (Blueprint $table) {
            // Risk Rating: Likelihood (1-5) x Impact (1-5) = Risk Score (1-25)
            $table->unsignedTinyInteger('likelihood')->nullable()->after('akibat');
            $table->unsignedTinyInteger('impact')->nullable()->after('likelihood');
            $table->unsignedTinyInteger('risk_score')->nullable()->after('impact');
            $table->string('risk_level', 20)->nullable()->after('risk_score');
        });
    }

    public function down(): void
    {
        Schema::table('incident_reports', function (Blueprint $table) {
            $table->dropColumn(['likelihood', 'impact', 'risk_score', 'risk_level']);
        });
    }
};
