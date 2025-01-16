<?php

use App\Models\Chapter;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Chapter::class)->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('content')->nullable();
            $table->boolean('is_free_preview')->default(0);
            $table->integer('order')->default(0);
            $table->string('type')->default('video');
            $table->morphs('lessonable');
            $table->timestamps();
        });

        Schema::table('lessons', function (Blueprint $table) {
            $table->string('lessonable_type')->nullable()->change();
            $table->unsignedBigInteger('lessonable_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->string('lessonable_type')->nullable(false)->change();
            $table->unsignedBigInteger('lessonable_id')->nullable(false)->change();
        });

        Schema::dropIfExists('lessons');
    }
};
