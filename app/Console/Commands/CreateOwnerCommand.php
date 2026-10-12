<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CreateOwnerCommand extends Command
{
    protected $signature = 'app:create-owner';
    protected $description = 'Create the first Owner account interactively';

    public function handle(): int
    {
        if (User::whereHas('staff.role', fn ($query) => $query->where('slug', 'owner'))->exists()) {
            $this->error('An Owner account already exists.');
            return self::FAILURE;
        }
        $name = trim((string) $this->ask('First name'));
        $last = trim((string) $this->ask('Last name'));
        $email = trim((string) $this->ask('Email'));
        $password = (string) $this->secret('Password (at least 12 characters)');
        if ($name === '' || $last === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 12) {
            $this->error('Name, valid email, and a password of at least 12 characters are required.');
            return self::FAILURE;
        }
        DB::transaction(function () use ($name, $last, $email, $password) {
            $role = Role::where('slug', 'owner')->firstOrFail();
            $staff = Staff::create(['code' => 'OWNER-1', 'first_name' => $name, 'last_name' => $last, 'email' => $email, 'role_id' => $role->id, 'type' => 'مستخدم', 'login_allowed' => true]);
            User::create(['staff_id' => $staff->id, 'name' => $staff->full_name, 'email' => $email, 'password' => Hash::make($password), 'active' => true]);
        });
        $this->info('Owner account created.');
        return self::SUCCESS;
    }
}
