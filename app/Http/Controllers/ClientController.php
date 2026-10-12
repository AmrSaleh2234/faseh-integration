<?php

namespace App\Http\Controllers;

use App\Actions\SaveClient;
use App\Actions\StoreAttachment;
use App\Models\Client;
use App\Services\LocalReadService;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(Request $request, LocalReadService $read) { return view('clients.index', ['clients' => $read->clients($request)]); }
    public function create() { return view('clients.form', ['client' => null]); }
    public function show(Request $request, Client $client, LocalReadService $read) { return view('clients.show', ['client' => $read->clientDetails($client, $request->user())]); }
    public function edit(Request $request, Client $client, LocalReadService $read) { return view('clients.form', ['client' => $read->clientDetails($client, $request->user())]); }
    public function store(Request $request, SaveClient $action, StoreAttachment $files)
    {
        $data = $this->validated($request);
        $client = $action->save($data, null, $request->user());
        foreach ($request->file('attachments', []) as $file) $files->store($client, $file, $request->user());
        return redirect()->route('clients.show', $client)->with('status', 'تم حفظ العميل.');
    }
    public function update(Request $request, Client $client, SaveClient $action, StoreAttachment $files)
    {
        $data = $this->validated($request, $client);
        $action->save($data, $client, $request->user());
        foreach ($request->file('attachments', []) as $file) $files->store($client, $file, $request->user());
        return redirect()->route('clients.show', $client)->with('status', 'تم تحديث العميل.');
    }
    public function destroy(Request $request, Client $client, SaveClient $action)
    {
        $action->archive($client, $request->user());
        return redirect()->route('clients.index')->with('status', 'تم أرشفة العميل.');
    }
    private function validated(Request $request, ?Client $client = null): array
    {
        return $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:clients,code,'.($client?->id ?? 'NULL')],
            'type' => ['required', 'in:individual,business'], 'business_name' => ['required', 'string', 'max:255'],
            'first_name' => ['nullable', 'string', 'max:255'], 'last_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'], 'mobile' => ['nullable', 'string', 'max:50'], 'email' => ['nullable', 'email', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'], 'country' => ['nullable', 'string', 'max:255'], 'classification' => ['nullable', 'string', 'max:255'],
            'tax_number' => ['nullable', 'string', 'max:50'], 'registration' => ['nullable', 'string', 'max:100'], 'billing' => ['nullable', 'string', 'max:50'],
            'language' => ['nullable', 'string', 'max:50'], 'government' => ['sometimes', 'boolean'], 'notes' => ['nullable', 'string'],
            'building' => ['nullable', 'string', 'max:255'], 'street' => ['nullable', 'string', 'max:255'], 'secondary_number' => ['nullable', 'string', 'max:255'],
            'district' => ['nullable', 'string', 'max:255'], 'postal' => ['nullable', 'string', 'max:50'], 'short_address' => ['nullable', 'string', 'max:255'], 'second_address' => ['nullable', 'string'],
            'contacts' => ['nullable', 'array'], 'contacts.*.name' => ['nullable', 'string', 'max:255'], 'contacts.*.email' => ['nullable', 'email', 'max:255'],
            'attachments' => ['nullable', 'array'], 'attachments.*' => ['file', 'max:10240'],
        ]);
    }
}
