<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;

class InvoicePolicy
{
    public function view(User $user, Invoice $invoice): bool { return $user->permits('invoices.view'); }
    public function update(User $user, Invoice $invoice): bool { return $user->permits('invoices.edit'); }
    public function delete(User $user, Invoice $invoice): bool { return $user->permits('invoices.delete'); }
    public function pay(User $user, Invoice $invoice): bool { return $user->permits('invoices.pay'); }
    public function sync(User $user, Invoice $invoice): bool { return $user->permits('invoices.edit') && $invoice->status === 'issued' && $invoice->fasahSync?->status === 'failed'; }
}
