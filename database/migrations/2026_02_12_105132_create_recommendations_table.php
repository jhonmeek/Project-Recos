<?php

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
        Schema::create('recommendations', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('order_number')->unique();
            $table->string('responsible_unit');
            $table->text('title');
            $table->date('due_date')->nullable();
            $table->boolean('is_immediate')->default(false);
            $table->date('completion_date')->nullable();
            $table->text('completion_note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recommendations');
    }
};
