<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Role;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Http\Request;

class LocalReadService
{
    public function invoices(Request $request)
    {
        $query = Invoice::with('client', 'fasahSync')->latest();
        if ($request->filled('q')) {
            $query->where(fn ($q) => $q->where('number', 'like', '%'.$request->query('q').'%')->orWhereHas('client', fn ($c) => $c->where('business_name', 'like', '%'.$request->query('q').'%'))->orWhereHas('fasahSync', fn ($sync) => $sync->where('fasah_invoice_number', 'like', '%'.$request->query('q').'%')));
        }
        if ($request->filled('client')) {
            $query->whereHas('client', fn ($q) => $q->where('business_name', 'like', '%'.$request->query('client').'%'));
        }
        if ($request->filled('number')) {
            $query->where(fn ($q) => $q->where('number', 'like', '%'.$request->query('number').'%')->orWhereHas('fasahSync', fn ($sync) => $sync->where('fasah_invoice_number', 'like', '%'.$request->query('number').'%')));
        }
        if ($request->filled('status')) {
            match ($request->query('status')) {
                'مسودة' => $query->where('status', 'draft'),
                'مدفوعة' => $query->where('status', 'issued')->whereColumn('amount_paid', '>=', 'total'),
                'غير مدفوعة' => $query->where('status', 'issued')->whereColumn('amount_paid', '<', 'total'),
                default => null,
            };
        }
        if ($request->filled('from')) {
            $query->whereDate('invoice_date', '>=', $request->query('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('invoice_date', '<=', $request->query('to'));
        }

        return $query->paginate(20)->withQueryString();
    }

    public function clients(Request $request)
    {
        return Client::query()->when($request->filled('name'), fn ($q) => $q->where('business_name', 'like', '%'.$request->query('name').'%'))
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->query('type')))
            ->when($request->filled('status'), fn ($q) => $q->whereNull('deleted_at'))
            ->when($request->filled('classification'), fn ($q) => $q->where('classification', $request->query('classification')))
            ->latest()->paginate(20)->withQueryString();
    }

    public function staff(Request $request)
    {
        return Staff::with('role')->when($request->filled('q'), fn ($q) => $q->where(fn ($s) => $s->where('first_name', 'like', '%'.$request->query('q').'%')->orWhere('last_name', 'like', '%'.$request->query('q').'%')->orWhere('code', 'like', '%'.$request->query('q').'%')->orWhere('email', 'like', '%'.$request->query('q').'%')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->query('status')))
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->query('type')))
            ->when($request->filled('role'), fn ($q) => $q->whereHas('role', fn ($r) => $r->where('name', 'like', '%'.$request->query('role').'%')))
            ->latest()->paginate(20)->withQueryString();
    }

    public function roles()
    {
        return Role::withCount('staff')->with('permissions')->get();
    }

    public function roleOptions()
    {
        return Role::orderBy('name')->get();
    }

    public function clientOptions()
    {
        return Client::orderBy('business_name')->get();
    }

    public function salesOptions()
    {
        return Staff::where('status', 'نشط')->orderBy('first_name')->get();
    }

    public function clientDetails(Client $client, User $actor): Client
    {
        $relations = ['contacts', 'attachments'];
        if ($actor->permits('invoices.view')) {
            $relations[] = 'invoices.payments';
        }

        return $client->load($relations);
    }

    public function invoiceDetails(Invoice $invoice): Invoice
    {
        return $invoice->load('client', 'salesStaff', 'items', 'customFields', 'payments', 'attachments', 'fasahSync');
    }

    public function invoiceForEditing(Invoice $invoice): Invoice
    {
        return $invoice->load('client', 'items', 'customFields', 'attachments', 'fasahSync');
    }

    public function activities(Request $request)
    {
        return ActivityLog::with('actor')->when($request->filled('q'), fn ($query) => $query->where(fn ($q) => $q->where('target', 'like', '%'.$request->query('q').'%')->orWhere('event', 'like', '%'.$request->query('q').'%')))
            ->when($request->filled('type'), fn ($q) => $q->where('subject_type', $request->query('type')))
            ->when($request->query('period') === 'today', fn ($q) => $q->whereDate('created_at', today()))
            ->when($request->query('period') === 'week', fn ($q) => $q->where('created_at', '>=', now()->subDays(7)))
            ->when($request->query('period') === 'month', fn ($q) => $q->where('created_at', '>=', now()->subDays(30)))
            ->latest()->paginate(20)->withQueryString();
    }

    public function dashboard(User $actor): array
    {
        $canInvoices = $actor->permits('invoices.view');
        $all = Invoice::where('status', 'issued');

        return [
            'invoiceCount' => $canInvoices ? Invoice::count() : 0,
            'clientCount' => $actor->permits('clients.view') ? Client::count() : 0,
            'staffCount' => $actor->permits('staff.view') ? Staff::count() : 0,
            'roleCount' => $actor->permits('roles.view') ? Role::count() : 0,
            'paidCount' => $canInvoices ? (clone $all)->whereColumn('amount_paid', '>=', 'total')->count() : 0,
            'unpaidCount' => $canInvoices ? (clone $all)->whereColumn('amount_paid', '<', 'total')->count() : 0,
            'totalAmount' => $canInvoices ? (clone $all)->sum('total') : 0,
            'paidAmount' => $canInvoices ? (clone $all)->sum('amount_paid') : 0,
            'recentInvoices' => $canInvoices ? Invoice::with('client')->latest()->take(5)->get() : collect(),
            'daily' => $canInvoices ? Invoice::where('status', 'issued')->selectRaw('invoice_date, SUM(total) as total, SUM(amount_paid) as paid')->groupBy('invoice_date')->orderBy('invoice_date')->get() : collect(),
            'activities' => $actor->permits('activity.view') ? ActivityLog::with('actor')->latest()->take(4)->get() : collect(),
        ];
    }
}
