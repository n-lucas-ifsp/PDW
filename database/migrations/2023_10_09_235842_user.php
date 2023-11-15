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
        Schema::create('user', function (Blueprint $table){
            $table->bigIncrements('id');
            $table->boolean('active')->nullable(false);
            $table->tinyInteger('sys_level')->nullable(false);
            $table->tinyInteger('sys_role')->nullable(false);
            $table->string('username', 50)->nullable(false);
            $table->string('password', 60)->nullable(false);
            $table->string('person_name', 100)->nullable(false);
            $table->date('birthdate')->nullable(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user');
    }
};
