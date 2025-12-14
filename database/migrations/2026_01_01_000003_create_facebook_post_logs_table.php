<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('facebook_post_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('facebook_user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('facebook_page_id')->nullable()->constrained()->nullOnDelete();
            $table->string('job_id')->nullable();
            $table->string('type');
            $table->text('message')->nullable();
            $table->string('media_path')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->string('status')->default('queued');
            $table->json('response')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['status', 'scheduled_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facebook_post_logs');
    }
};
