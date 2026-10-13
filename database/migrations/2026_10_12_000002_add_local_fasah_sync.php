<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('fasah_type')->nullable();
            $table->json('fasah_meta')->nullable();
        });

        Schema::create('local_fasah_syncs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->unique()->constrained()->restrictOnDelete();
            $table->string('internal_invoice_number')->unique();
            $table->string('fasah_invoice_number')->nullable()->unique();
            $table->string('status')->default('pending')->index();
            $table->string('fasah_status')->nullable();
            $table->string('sadad_number')->nullable();
            $table->unsignedInteger('attempts')->default(0);
            $table->json('request_payload')->nullable();
            $table->json('fasah_response')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('settled_at')->nullable();
            $table->timestamps();
        });

        Schema::table('invoice_payments', function (Blueprint $table) {
            $table->string('external_key')->nullable()->unique();
        });
    }

    public function down(): void
    {
        Schema::table('invoice_payments', fn (Blueprint $table) => $table->dropColumn('external_key'));
        Schema::dropIfExists('local_fasah_syncs');
        Schema::table('invoices', fn (Blueprint $table) => $table->dropColumn(['fasah_type', 'fasah_meta']));
    }
};
