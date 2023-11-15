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
        Schema::create('product', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('id_category');
            $table->unsignedBigInteger('id_seller');

            $table->boolean('active')->nullable(false);
            $table->boolean('already_selled')->nullable(false);

            $table->string('identifier', 13)->nullable(false);

            $table->decimal('price');
            $table->string('title', 50)->nullable(false);
            $table->string('author', 255)->nullable(false);

            $table->string('brief_desc', 255)->nullable(false);
            $table->string('person_desc', 255)->nullable(false);
            
            $table->timestamps();

            $table->foreign('id_category')->references('id')->on('category');
            $table->foreign('id_seller')->references('id')->on('user');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product');
    }
};
