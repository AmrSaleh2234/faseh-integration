<?php

namespace App\Console\Commands;

use App\Services\FasahPay\FasahPayAuthService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class ProbeFasahInvoiceUrlCommand extends Command
{
    protected $signature = 'fasah:probe-invoice-url';

    protected $description = 'Try several likely Fasah Pay invoice API base URL/path variants to find the correct one';

    public function handle(FasahPayAuthService $auth): int
    {
        $cfg = $auth->credentials();
        $token = $auth->token();
        $configuredBase = rtrim($cfg['invoice_base_url'], '/');

        // Strip any trailing known resource segments to get gateway roots to try.
        $roots = array_unique([
            $configuredBase,
            preg_replace('#/invoices$#', '', $configuredBase),
            preg_replace('#/v1\.1/#', '/v1/', $configuredBase),
            preg_replace('#/v1\.1/#', '/v2/', $configuredBase),
            preg_replace('#/invoices$#', '', preg_replace('#/v1\.1/#', '/v1/', $configuredBase)),
        ]);

        $this->line('Client-Id header value : '.$cfg['client_id']);
        $this->line('Client-Secret header    : '.substr($cfg['client_secret'], 0, 4).'****'.substr($cfg['client_secret'], -4));
        $this->line('Authorization header    : Bearer '.substr($token, 0, 20).'...'.substr($token, -10));
        $this->line('');

        $rows = [];

        foreach ($roots as $root) {
            $url = $root.'/billoflading';

            $response = Http::timeout(15)
                ->acceptJson()
                ->withHeaders([
                    'X-Tabadul-Client-Id' => $cfg['client_id'],
                    'X-Tabadul-Client-Secret' => $cfg['client_secret'],
                    'Authorization' => 'Bearer '.$token,
                ])
                ->post($url, []);

            $rows[] = [$url, $response->status(), substr($response->body(), 0, 120)];
        }

        $this->table(['URL tried', 'HTTP Status', 'Body (first 120 chars)'], $rows);

        $this->line('');
        $this->info('A 400/422 (validation error, since we sent an empty body) means the PATH is correct.');
        $this->info('A 404 means that path/base is wrong. A 401/403 means auth issue on that path.');

        return self::SUCCESS;
    }
}
