<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Role;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceDocumentTest extends TestCase
{
    use RefreshDatabase;

    private function owner(): User
    {
        $this->seed();
        $role = Role::where('slug', 'owner')->firstOrFail();
        $staff = Staff::create(['code' => 'PDF-OWNER', 'first_name' => 'PDF', 'last_name' => 'Owner', 'role_id' => $role->id, 'login_allowed' => true]);

        return User::create(['staff_id' => $staff->id, 'name' => 'PDF Owner', 'email' => 'pdf-owner@example.test', 'password' => 'test-password-123', 'active' => true]);
    }

    public function test_issued_invoice_has_a_data_driven_preview_and_downloadable_pdf(): void
    {
        $owner = $this->owner();
        $client = Client::create(['code' => 'PDF-CLIENT', 'type' => 'business', 'business_name' => 'شركة العميل للتجارة', 'tax_number' => '123456789012345', 'city' => 'جدة']);
        $invoice = Invoice::create([
            'client_id' => $client->id,
            'created_by' => $owner->id,
            'number' => 146,
            'status' => 'issued',
            'invoice_date' => '2026-10-11',
            'issue_date' => '2026-10-11',
            'issued_at' => '2026-10-11 12:00:00',
            'payment_terms' => 7,
            'subtotal' => '115.00',
            'tax_total' => '15.00',
            'total' => '115.00',
            'amount_paid' => '20.00',
            'notes' => 'شروط الدفع خلال سبعة أيام',
        ]);
        $invoice->items()->create(['position' => 0, 'name' => 'Ocean Freight', 'description' => 'شحن بحري', 'unit_price' => '100.00', 'quantity' => 1, 'discount' => '0.00', 'tax_one' => 15, 'tax_two' => 0, 'line_total' => '115.00']);
        $invoice->customFields()->create(['position' => 0, 'name' => 'BL#', 'value' => 'BL-2026-146']);
        $invoice->fasahSync()->create(['internal_invoice_number' => 'LOCAL-146', 'fasah_invoice_number' => 'FP-123', 'status' => 'synced']);
        config([
            'invoice_document.company.vat_number' => '312836247600003',
            'invoice_document.banks.sar.iban' => 'SA3110000085200001551710',
        ]);

        $this->get(route('invoices.pdf', $invoice))->assertRedirect(route('login'));

        $this->actingAs($owner)->get(route('invoices.document', $invoice))
            ->assertOk()
            ->assertSee('Invoice No #146')
            ->assertSee('شركة العميل للتجارة')
            ->assertSee('BL-2026-146')
            ->assertSee('FP-123')
            ->assertSee('SAR 100.00')
            ->assertSee('18/10/2026')
            ->assertSee('data:image/png;base64,', false)
            ->assertSee('SA3110000085200001551710');

        $response = $this->get(route('invoices.pdf', $invoice))->assertOk();
        $this->assertStringStartsWith('%PDF-', $response->getContent());
        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('invoice-146.pdf', $response->headers->get('Content-Disposition'));
    }

    public function test_draft_can_be_previewed_but_not_downloaded_as_issued_pdf(): void
    {
        $owner = $this->owner();
        $invoice = Invoice::create(['created_by' => $owner->id, 'status' => 'draft']);

        $this->actingAs($owner)->get(route('invoices.document', $invoice))->assertOk();
        $this->get(route('invoices.pdf', $invoice))->assertNotFound();
    }
}
