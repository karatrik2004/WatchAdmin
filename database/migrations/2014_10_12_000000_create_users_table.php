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

        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned(); // Auto-incrementing ID
            $table->integer('role_id')->default(1)->comment('10=Admin');
            $table->string('firstname')->nullable();
            $table->string('lastname')->nullable();
            $table->string('email')->unique(); // Unique email
            $table->string('password');
            $table->string('mobile')->nullable();
            $table->boolean('status')->default(1)->comment('1=Active, 0=Deactive');
            $table->dateTime('last_seen')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps(); // Creates `created_at` and `updated_at` columns
            $table->softDeletes(); // Creates `deleted_at` column for soft deletes
        });       
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
