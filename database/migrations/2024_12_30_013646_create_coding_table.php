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
        Schema::create('codings', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('language')->nullable();
            $table->json('hints')->nullable();
            $table->text('instruct')->nullable();
            $table->text('solution_code')->nullable();
            $table->longText('code')->nullable();
            $table->longText('student_code')->nullable();
            $table->longText('test_case')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('codings');
    }
};
