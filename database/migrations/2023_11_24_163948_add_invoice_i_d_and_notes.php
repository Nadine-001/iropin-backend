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

        Schema::table('participants', function (Blueprint $table) {
            $table->foreignId('invoice_id')->after('webinar_id')->index();
            $table->foreign('invoice_id')->references('id')->on('invoices');
            $table->boolean('status')->nullable()->default(0)->after('invoice_id');
            $table->string('note')->nullable()->after('status');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->boolean('is_checked')->nullable()->default(0)->after('file_name');
        });

        Schema::table('files', function (Blueprint $table) {
            $table->boolean('is_checked')->nullable()->default(0)->after('file_name');
        });

        Schema::table('licences', function (Blueprint $table) {
            $table->string('note')->nullable()->after('status');
        });

        Schema::table('registration_payments', function (Blueprint $table) {
            $table->boolean('is_checked')->nullable()->default(0)->after('payment_receipt');
            $table->string('note')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            $table->dropForeign('invoice_id');
            $table->dropColumn('invoice_id');
            $table->dropColumn('status');
            $table->dropColumn('note');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('is_checked');
        });

        Schema::table('files', function (Blueprint $table) {
            $table->dropColumn('is_checked');
        });

        Schema::table('licences', function (Blueprint $table) {
            $table->dropColumn('note');
        });

        Schema::table('registration_payments', function (Blueprint $table) {
            $table->dropColumn('is_checked');
            $table->dropColumn('note');
        });
    }
};
