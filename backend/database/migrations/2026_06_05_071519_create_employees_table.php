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
         Schema::create('employees', function (Blueprint $table) {
        $table->id();

        // Required fields
        $table->string('firstname');
        $table->string('lastname');

        // Foreign key to factories table
        $table->foreignId('factory_id')
              ->constrained('factories')
              ->cascadeOnDelete();

        // Optional fields
        $table->string('email')->nullable();
        $table->string('phone')->nullable();

        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }

};
