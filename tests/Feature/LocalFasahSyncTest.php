<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\LocalFasahSync;
use App\Models\Role;
use App\Models\Staff;
use App\Models\User;
use App\Jobs\SyncLocalInvoiceToFasahJob;
use App\Services\FasahPay\SyncLocalInvoice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class LocalFasahSyncTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
    }

    private function owner(): User
    {
        $this->seed();
        $staff = Staff::create(['code' => 'OWNER-FASAH', 'first_name' => 'Test', 'last_name' => 'Owner', 'role_id' => Role::where('slug', 'owner')->firstOrFail()->id, 'login_allowed' => true]);
        return User::create(['staff_id' => $staff->id, 'name' => 'Test Owner', 'email' => 'fasah-owner@example.test', 'password' => 'test-password-123', 'active' => true]);
    }

    private function client(): Client
    {
        return Client::create(['code' => 'FASAH-CLIENT', 'type' => 'business', 'business_name' => 'Local Shipping', 'email' => 'buyer@example.test', 'mobile' => '0500000000']);
    }

    private function issue(Client $client, string $type, array $meta = [], array $extra = []): Invoice
    {
        $this->post(route('invoices.store'), array_merge([
            'action' => 'issue',
            'client_id' => $client->id,
            'invoice_date' => '2026-10-12',
            'issue_date' => '2026-10-12',
            'fasah_type' => $type,
            'fasah_meta' => $meta,
            'items' => [['name' => 'Freight', 'unit_price' => 100, 'quantity' => 1, 'tax_one' => 15]],
        ], $extra))->assertRedirect();

        return Invoice::latest('id')->firstOrFail();
    }

    private function configureSandbox(): void
    {
        config([
            'fasahpay.env' => 'sandbox',
            'fasahpay.sandbox.token_url' => 'https://fasah.e2e.invalid/token',
            'fasahpay.sandbox.invoice_base_url' => 'https://fasah.e2e.invalid/invoices',
            'fasahpay.sandbox.client_id' => 'test-client',
            'fasahpay.sandbox.client_secret' => 'test-secret',
            'fasahpay.sandbox.username' => 'test-user',
            'fasahpay.sandbox.password' => 'test-password',
            'fasahpay.biller_vat_number' => '123456789012345',
            'fasahpay.callback_secret' => 'callback-secret',
        ]);
        Cache::put('fasahpay.jwt.sandbox', 'fake-token', 3000);
        Http::preventStrayRequests();
    }

    public function test_draft_is_not_sent_and_each_supported_type_can_be_issued(): void
    {
        $this->actingAs($this->owner());
        $client = $this->client();
        $this->post(route('invoices.store'), ['action' => 'draft'])->assertRedirect();
        $this->assertNull(Invoice::firstOrFail()->fasahSync);

        $cases = [
            'general' => [],
            'billoflading' => ['bill_of_lading' => 'BL-1', 'shipment_type' => '1', 'port' => '30', 'doc_ref_no' => 'DOC-1'],
            'declaration' => ['declaration_number' => 'DEC-1', 'declaration_date' => '2026-10-12', 'declaration_type' => '1', 'port' => '30'],
            'custombroker' => ['customs_broker_license' => 'LIC-1', 'license_type' => '1', 'port' => '30'],
            'importer' => ['importer_number' => 'IMP-1', 'port' => '30'],
            'shippingagent' => ['shipping_agent_number' => 'AGENT-1', 'port' => '30'],
        ];
        foreach ($cases as $type => $meta) {
            $invoice = $this->issue($client, $type, $meta);
            $this->assertSame('issued', $invoice->status);
            $this->assertSame('pending', $invoice->fasahSync->status);
            $this->assertSame('LOCAL-'.$invoice->number, $invoice->fasahSync->internal_invoice_number);
        }
        $this->assertSame(6, LocalFasahSync::count());
        Queue::assertPushed(SyncLocalInvoiceToFasahJob::class, 6);
        Queue::assertPushed(SyncLocalInvoiceToFasahJob::class, fn ($job) => $job->queue === 'fasah-local');

        $this->configureSandbox();
        Http::fake(fn ($request) => Http::response([
            'statusCode' => '00000',
            'fasahpayInvoiceNumber' => 'F-'.$request->data()['internalInvoiceNumber'],
        ]));
        foreach (Invoice::where('status', 'issued')->get() as $invoice) {
            app(SyncLocalInvoice::class)->execute($invoice->id);
            $this->assertSame('synced', $invoice->fasahSync->fresh()->status);
            $this->assertSame('LOCAL-'.$invoice->number, $invoice->fasahSync->fresh()->request_payload['internalInvoiceNumber']);
        }
        Http::assertSentCount(6);
    }

    public function test_missing_fasah_fields_reject_issue_but_allow_draft(): void
    {
        $this->actingAs($this->owner());
        $client = $this->client();
        $data = ['action' => 'issue', 'client_id' => $client->id, 'invoice_date' => '2026-10-12', 'issue_date' => '2026-10-12', 'fasah_type' => 'billoflading', 'items' => [['name' => 'Freight', 'unit_price' => 100, 'quantity' => 1]]];
        $this->post(route('invoices.store'), $data)->assertSessionHasErrors('fasah_meta.bill_of_lading');
        $this->assertSame(0, LocalFasahSync::count());
        $this->post(route('invoices.store'), array_merge($data, ['fasah_meta' => ['bill_of_lading' => 'BL-1', 'shipment_type' => 'invalid', 'port' => '30', 'doc_ref_no' => 'DOC-1']]))->assertSessionHasErrors('fasah_meta.shipment_type');
        $this->post(route('invoices.store'), array_merge($data, ['action' => 'draft']))->assertRedirect();
        $this->assertSame('draft', Invoice::firstOrFail()->status);
    }

    public function test_success_requires_fasah_number_and_retry_is_authorized(): void
    {
        $owner = $this->owner();
        $this->actingAs($owner);
        $invoice = $this->issue($this->client(), 'general');
        $this->get(route('invoices.show', $invoice))->assertOk()->assertSee('قيد المزامنة')->assertDontSee('تمت المزامنة');
        $this->configureSandbox();
        Http::fake(['https://fasah.e2e.invalid/invoices/*' => Http::sequence()
            ->push(['statusCode' => '00000'])
            ->push(['statusCode' => '00001', 'statusDesc' => 'Rejected'])
            ->push(['statusCode' => '00000', 'fasahpayInvoiceNumber' => 'F-2'])]);
        app(SyncLocalInvoice::class)->execute($invoice->id);
        $this->assertSame('uncertain', $invoice->fasahSync->fresh()->status, $invoice->fasahSync->fresh()->last_error ?? '');
        $this->post(route('invoices.fasah.retry', $invoice))->assertForbidden();

        $second = $this->issue($invoice->client, 'general');
        app(SyncLocalInvoice::class)->execute($second->id);
        $this->assertSame('failed', $second->fasahSync->fresh()->status);
        $this->post(route('invoices.fasah.retry', $second))->assertRedirect();
        $this->assertSame('pending', $second->fasahSync->fresh()->status);
        app(SyncLocalInvoice::class)->execute($second->id);
        $this->assertSame('synced', $second->fasahSync->fresh()->status);
        $this->get(route('invoices.show', $second))->assertSee('تمت المزامنة')->assertSee('F-2');
        $this->assertSame('synced', app(SyncLocalInvoice::class)->execute($second->id)->status);

        $staff = Staff::create(['code' => 'LIMITED-FASAH', 'first_name' => 'Limited', 'last_name' => 'User', 'role_id' => Role::where('slug', 'employee')->firstOrFail()->id, 'login_allowed' => true]);
        $limited = User::create(['staff_id' => $staff->id, 'name' => 'Limited', 'email' => 'limited-fasah@example.test', 'password' => 'test-password-123', 'active' => true]);
        $this->actingAs($limited)->get(route('invoices.fasah.status', $second))->assertOk();
        $this->post(route('invoices.fasah.retry', $second))->assertForbidden();
    }

    public function test_connection_failure_is_uncertain_and_paid_callback_is_idempotent(): void
    {
        $this->actingAs($this->owner());
        $invoice = $this->issue($this->client(), 'general', [], ['deposit_amount' => 20, 'deposit_paid' => 1]);
        $this->configureSandbox();
        Http::fake(fn () => throw new ConnectionException('connection lost'));
        app(SyncLocalInvoice::class)->execute($invoice->id);
        $this->assertSame('uncertain', $invoice->fasahSync->fresh()->status);
        $this->assertEquals(95, $invoice->fasahSync->fresh()->request_payload['grandTotal']);
        $this->assertEquals(12.39, $invoice->fasahSync->fresh()->request_payload['totalVAT']);

        $invoice->fasahSync->update(['status' => 'synced', 'fasah_invoice_number' => 'F-PAID']);
        $paid = ['fasahpayInvoiceNumber' => 'F-PAID', 'invoiceStatus' => 'PAID', 'sadadNumber' => 'S-1'];
        $this->withHeader('X-Callback-Secret', 'callback-secret')->postJson('/api/fasah/callbacks/invoice-notification', $paid)->assertOk();
        $this->postJson('/api/fasah/callbacks/invoice-notification', $paid)->assertOk();
        $this->assertSame(1, $invoice->payments()->where('method', 'Fasah Pay')->count());
        $this->assertEquals(115, $invoice->fresh()->amount_paid);
        $this->assertEquals(95, $invoice->payments()->where('method', 'Fasah Pay')->firstOrFail()->amount);
        $this->postJson('/api/fasah/callbacks/settlement-notification', ['invoiceNumber' => 'F-PAID', 'sadadNumber' => 'S-1'])->assertOk();
        $this->assertNotNull($invoice->fasahSync->fresh()->settled_at);
    }

    public function test_http_rejection_can_be_retried_but_server_error_cannot(): void
    {
        $this->actingAs($this->owner());
        $client = $this->client();
        $rejected = $this->issue($client, 'general');
        $uncertain = $this->issue($client, 'general');
        $this->configureSandbox();
        Http::fake(['https://fasah.e2e.invalid/invoices/*' => Http::sequence()
            ->push(['message' => 'bad input'], 400)
            ->push(['message' => 'upstream error'], 503)]);

        app(SyncLocalInvoice::class)->execute($rejected->id);
        app(SyncLocalInvoice::class)->execute($uncertain->id);
        $this->assertSame('failed', $rejected->fasahSync->fresh()->status);
        $this->assertSame('uncertain', $uncertain->fasahSync->fresh()->status);
        $this->post(route('invoices.fasah.retry', $uncertain))->assertForbidden();
        app(SyncLocalInvoice::class)->execute($uncertain->id);
        Http::assertSentCount(2);
    }

    public function test_missing_sandbox_credentials_fail_without_outbound_request(): void
    {
        $this->actingAs($this->owner());
        $invoice = $this->issue($this->client(), 'general');
        config(['fasahpay.biller_vat_number' => '123456789012345', 'fasahpay.sandbox.client_id' => '']);
        Http::fake();
        Http::preventStrayRequests();

        app(SyncLocalInvoice::class)->execute($invoice->id);
        $this->assertSame('failed', $invoice->fasahSync->fresh()->status);
        $this->assertStringContainsString('بيانات دخول Fasah', $invoice->fasahSync->fresh()->last_error);
        Http::assertNothingSent();
    }

    public function test_local_callback_without_secret_is_not_acknowledged_or_paid(): void
    {
        $this->actingAs($this->owner());
        $invoice = $this->issue($this->client(), 'general');
        $invoice->fasahSync->update(['status' => 'synced', 'fasah_invoice_number' => 'F-NO-SECRET']);
        config(['fasahpay.callback_secret' => '']);

        $this->postJson('/api/fasah/callbacks/invoice-notification', [
            'fasahpayInvoiceNumber' => 'F-NO-SECRET',
            'invoiceStatus' => 'PAID',
        ])->assertStatus(503);
        $this->assertEquals(0, $invoice->fresh()->amount_paid);
        $this->assertSame(0, $invoice->payments()->count());
    }
}
