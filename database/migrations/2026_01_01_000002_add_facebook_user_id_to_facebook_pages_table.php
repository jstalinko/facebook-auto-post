<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('facebook_pages', function (Blueprint $table) {
            $table->foreignId('facebook_user_id')
                ->nullable()
                ->after('user_id')
                ->constrained('facebook_users')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('facebook_pages', function (Blueprint $table) {
            $table->dropConstrainedForeignId('facebook_user_id');
        });
    }
};
