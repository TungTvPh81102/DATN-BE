<?php

use App\Models\LiveStreamCredential;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('live_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(LiveStreamCredential::class)->nullable()->constrained('live_stream_credentials')->cascadeOnDelete();
            $table->foreignId('instructor_id')->constrained('users')->cascadeOnDelete();
            $table->string('code')->unique();
            $table->string('title')->nullable();
            $table->string('thumbnail')->nullable();
            $table->text('description')->nullable();
            $table->enum('visibility', ['public', 'private'])->default('public');
            $table->enum('status', ['upcoming', 'live', 'ended'])->default('upcoming');
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('actual_start_time')->nullable();
            $table->dateTime('actual_end_time')->nullable();
            $table->string('recording_asset_id')->nullable();
            $table->string('recording_playback_id')->nullable();
            $table->string('duration')->nullable();
            $table->string('recording_url')->nullable();
            $table->integer('viewers_count')->nullable()->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('live_sessions');
    }
};
