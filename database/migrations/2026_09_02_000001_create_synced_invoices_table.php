<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('synced_invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('daftra_invoice_id')->unique();
            $table->string('daftra_invoice_number')->nullable()->index();
            $table->string('internal_invoice_number')->index();
            $table->string('invoice_type')->default('billoflading');
            $table->string('fasah_invoice_number')->nullable()->index();
            $table->string('sadad_number')->nullable()->index();
            $table->string('status')->default('pending')->index();
            $table->string('fasah_status')->nullable();
            $table->decimal('grand_total', 14, 2)->nullable();
            $table->decimal('total_vat', 14, 2)->nullable();
            $table->json('logistics_meta')->nullable();
            $table->json('request_payload')->nullable();
            $table->json('fasah_response')->nullable();
            $table->boolean('daftra_marked_paid')->default(false);
            $table->timestamp('synced_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('settled_at')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('synced_invoices');
    }
};
