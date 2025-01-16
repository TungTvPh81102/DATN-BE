<?php

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
            $table->foreignId('instructor_id')->constrained('users')->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->string('description')->nullable();
            $table->string('stream_key')->nullable();
            $table->string('mux_playback_id')->nullable();
            $table->enum('status', ['Đang diễn ra', 'Kết thúc', 'Sắp diễn ra', 'Đã hủy'])
                ->default('Đang diễn ra');
            $table->dateTime('start_time')->nullable();
            $table->dateTime('end_time')->nullable();
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
