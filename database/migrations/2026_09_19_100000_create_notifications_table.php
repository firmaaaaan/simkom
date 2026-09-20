<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('message');
            $table->string('type', 30)->default('info');   // ticket | borrowing
            $table->string('url')->nullable();             // tujuan saat diklik
            $table->timestamp('read_at')->nullable();      // global: penanda "read pointer" paling baru
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('notifications_read_at')->nullable()->after('remember_token');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('notifications_read_at');
        });
    }
};
