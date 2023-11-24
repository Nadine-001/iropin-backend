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
        Schema::create('licence_form_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('licence_id')->index();
            $table->foreign('licence_id')->references('id')->on('licences');
            $table->string('key');
            $table->string('val');
            $table->foreignId('file_id')->index();
            $table->foreign('file_id')->references('id')->on('files');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('licence_form_details');
    }
};
