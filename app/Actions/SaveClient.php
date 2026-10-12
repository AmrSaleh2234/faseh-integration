<?php

namespace App\Actions;

use App\Models\Client;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SaveClient
{
    public function __construct(private RecordActivity $activity) {}

    public function save(array $data, ?Client $client, User $actor): Client
    {
        return DB::transaction(function () use ($data, $client, $actor) {
            $client ??= new Client();
            $client->fill(collect($data)->only(['type', 'business_name', 'first_name', 'last_name', 'phone', 'mobile', 'email', 'city', 'country', 'classification', 'tax_number', 'registration', 'billing', 'language', 'code', 'notes'])->all());
            $client->government = (bool) ($data['government'] ?? false);
            $client->address = collect($data)->only(['building', 'street', 'secondary_number', 'district', 'postal', 'short_address', 'second_address'])->all();
            $client->save();
            $client->contacts()->delete();
            foreach ($data['contacts'] ?? [] as $contact) {
                if (trim($contact['name'] ?? '') !== '') $client->contacts()->create(['name' => $contact['name'], 'email' => $contact['email'] ?? null]);
            }
            $this->activity->record($actor, 'client.saved', 'client', $client->id, $client->business_name);
            return $client;
        });
    }

    public function archive(Client $client, User $actor): void
    {
        DB::transaction(function () use ($client, $actor) {
            $client->delete();
            $this->activity->record($actor, 'client.archived', 'client', $client->id, $client->business_name);
        });
    }
}
