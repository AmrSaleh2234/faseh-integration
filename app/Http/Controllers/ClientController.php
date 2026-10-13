<?php

namespace App\Http\Controllers;

use App\Actions\SaveClient;
use App\Actions\StoreAttachment;
use App\Http\Requests\ClientFormRequest;
use App\Models\Client;
use App\Services\FasahPay\ClientReadiness;
use App\Services\LocalReadService;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(Request $request, LocalReadService $read)
    {
        return view('clients.index', ['clients' => $read->clients($request)]);
    }

    public function create(ClientReadiness $readiness)
    {
        return view('clients.form', ['client' => null, 'fasahIssues' => $readiness->issues([])]);
    }

    public function show(Request $request, Client $client, LocalReadService $read, ClientReadiness $readiness)
    {
        return view('clients.show', ['client' => $read->clientDetails($client, $request->user()), 'fasahIssues' => $readiness->issues($client)]);
    }

    public function edit(Request $request, Client $client, LocalReadService $read, ClientReadiness $readiness)
    {
        return view('clients.form', ['client' => $read->clientDetails($client, $request->user()), 'fasahIssues' => $readiness->issues($client)]);
    }

    public function store(ClientFormRequest $request, SaveClient $action, StoreAttachment $files)
    {
        $data = $request->validated();
        $client = $action->save($data, null, $request->user());
        foreach ($request->file('attachments', []) as $file) {
            $files->store($client, $file, $request->user());
        }

        return redirect()->route('clients.show', $client)->with('status', 'تم حفظ العميل.');
    }

    public function update(ClientFormRequest $request, Client $client, SaveClient $action, StoreAttachment $files)
    {
        $data = $request->validated();
        $action->save($data, $client, $request->user());
        foreach ($request->file('attachments', []) as $file) {
            $files->store($client, $file, $request->user());
        }

        return redirect()->route('clients.show', $client)->with('status', 'تم تحديث العميل.');
    }

    public function destroy(Request $request, Client $client, SaveClient $action)
    {
        $action->archive($client, $request->user());

        return redirect()->route('clients.index')->with('status', 'تم أرشفة العميل.');
    }
}
