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


            // Required fields

 Schema::create('roles', function (Blueprint $table) {
    $table->id();
    $table->string('name'); // admin, user, staff
    $table->timestamps();
});




        Schema::create('users', function (Blueprint $table) {

        // Required fields
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');


    // Role relationship
    $table->foreignId('role_id')
          ->constrained('roles')
          ->cascadeOnDelete();

    // Optional fields (nullable)
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamps();



        });
  


        // Schema::create('password_reset_tokens', function (Blueprint $table) {
        //     $table->string('email')->primary();
        //     $table->string('token');
        //     $table->timestamp('created_at')->nullable();
        // });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('password_reset_tokens');
    }
};
