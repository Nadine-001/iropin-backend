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
        Schema::create('registration_form_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_id')->index();
            $table->foreign('registration_id')->references('id')->on('registrations');
            $table->foreignId('document_id')->index();
            $table->foreign('document_id')->references('id')->on('documents');
            $table->string('key');
            $table->string('val');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registration_form_details');
    }
};
