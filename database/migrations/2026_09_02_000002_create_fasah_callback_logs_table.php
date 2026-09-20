<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fasah_callback_logs', function (Blueprint $table) {
            $table->id();
            $table->string('type')->index(); // invoice | settlement
            $table->string('fasah_invoice_number')->nullable()->index();
            $table->string('internal_invoice_number')->nullable()->index();
            $table->string('sadad_number')->nullable();
            $table->string('invoice_status')->nullable();
            $table->json('payload');
            $table->boolean('processed')->default(false);
            $table->text('processing_error')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fasah_callback_logs');
    }
};
