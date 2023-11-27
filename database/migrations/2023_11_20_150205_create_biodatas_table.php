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
        Schema::create('biodatas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->index();
            $table->foreign('user_id')->references('id')->on('users');
            $table->string('name');
            $table->string('prefix')->nullable();
            $table->string('sufix')->nullable();
            $table->int('NIK');
            $table->string('birthplace');
            $table->date('birthdate');
            $table->string('gender');
            $table->string('religion')->nullable();
            $table->string('mobile_phone');
            $table->string('whatsapp_number');
            $table->string('STR_number');
            $table->date('publish_date');
            $table->date('exp_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('biodatas');
    }
};
