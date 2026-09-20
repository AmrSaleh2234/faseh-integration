<?php

namespace App\Console\Commands;

use App\Services\FasahPay\FasahPayAuthService;
use Illuminate\Console\Command;

class TestFasahAuthCommand extends Command
{
    protected $signature = 'fasah:test-auth';

    protected $description = 'Request a Fasah Pay JWT using .env credentials';

    public function handle(FasahPayAuthService $auth): int
    {
        try {
            $auth->forgetToken();
            $token = $auth->requestToken();
            $this->info('Token OK (length '.strlen($token).')');
            $this->line(substr($token, 0, 40).'...');

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }
}
