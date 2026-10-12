<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name')->unique();
            $table->string('description')->nullable();
            $table->boolean('built_in')->default(false);
            $table->boolean('locked')->default(false);
            $table->timestamps();
        });
        Schema::create('role_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->string('permission');
            $table->unique(['role_id', 'permission']);
        });
        Schema::create('staff', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('email')->nullable()->unique();
            $table->string('type')->default('موظف');
            $table->string('language')->default('العربية');
            $table->string('status')->default('نشط')->index();
            $table->foreignId('role_id')->constrained()->restrictOnDelete();
            $table->boolean('login_allowed')->default(false);
            $table->string('photo_path')->nullable();
            $table->text('notes')->nullable();
            $table->json('extra')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('staff_id')->nullable()->unique()->after('id')->constrained('staff')->nullOnDelete();
            $table->boolean('must_change_password')->default(false);
            $table->boolean('active')->default(true);
        });
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('type')->default('individual');
            $table->string('business_name');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('mobile')->nullable();
            $table->string('email')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->string('classification')->nullable();
            $table->string('tax_number')->nullable();
            $table->string('registration')->nullable();
            $table->string('billing')->nullable();
            $table->string('language')->nullable();
            $table->boolean('government')->default(false);
            $table->json('address')->nullable();
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
        Schema::create('client_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('email')->nullable();
            $table->timestamps();
        });
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('number')->nullable()->unique();
            $table->foreignId('client_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('sales_staff_id')->nullable()->constrained('staff')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('draft')->index();
            $table->date('invoice_date')->nullable()->index();
            $table->date('issue_date')->nullable();
            $table->unsignedInteger('payment_terms')->default(0);
            $table->string('delivery_method')->default('طباعة');
            $table->string('currency', 3)->default('SAR');
            $table->decimal('discount_value', 14, 2)->default(0);
            $table->string('discount_kind')->default('amount');
            $table->decimal('adjustment', 14, 2)->default(0);
            $table->decimal('deposit_amount', 14, 2)->default(0);
            $table->string('deposit_kind')->default('amount');
            $table->boolean('deposit_paid')->default(false);
            $table->boolean('insurance_visible')->default(false);
            $table->decimal('insurance_expenses', 14, 2)->default(0);
            $table->string('insurance_company')->nullable();
            $table->text('notes')->nullable();
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('tax_total', 14, 2)->default(0);
            $table->decimal('total', 14, 2)->default(0);
            $table->decimal('amount_paid', 14, 2)->default(0);
            $table->timestamp('issued_at')->nullable();
            $table->timestamps();
        });
        Schema::create('invoice_sequences', function (Blueprint $table) {
            $table->unsignedTinyInteger('id')->primary();
            $table->unsignedBigInteger('next_number');
        });
        \Illuminate\Support\Facades\DB::table('invoice_sequences')->insert(['id' => 1, 'next_number' => 1]);
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('position');
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('unit_price', 14, 2);
            $table->decimal('quantity', 14, 2);
            $table->decimal('discount', 14, 2)->default(0);
            $table->decimal('tax_one', 5, 2)->default(0);
            $table->decimal('tax_two', 5, 2)->default(0);
            $table->decimal('line_total', 14, 2);
            $table->timestamps();
        });
        Schema::create('invoice_custom_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('position');
            $table->string('name');
            $table->text('value')->nullable();
        });
        Schema::create('invoice_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->restrictOnDelete();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('amount', 14, 2);
            $table->date('paid_on');
            $table->string('method');
            $table->string('reference')->nullable();
            $table->timestamps();
        });
        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            $table->morphs('attachable');
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('disk')->default('local');
            $table->string('path');
            $table->string('original_name');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size');
            $table->timestamps();
        });
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('event')->index();
            $table->string('subject_type')->index();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('target');
            $table->text('summary')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['activity_logs', 'attachments', 'invoice_payments', 'invoice_custom_fields', 'invoice_items', 'invoice_sequences', 'invoices', 'client_contacts', 'clients'] as $name) Schema::dropIfExists($name);
        Schema::table('users', fn (Blueprint $table) => $table->dropConstrainedForeignId('staff_id'));
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn(['must_change_password', 'active']));
        Schema::dropIfExists('staff');
        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('roles');
    }
};
