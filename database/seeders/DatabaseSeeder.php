<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $roles = [
            'owner' => ['Owner', 'مالك النظام', config('local_permissions'), true],
            'sales' => ['موظف مبيعات', 'إدارة الفواتير والعملاء', ['invoices.view', 'invoices.create', 'invoices.edit', 'invoices.pay', 'clients.view', 'clients.create', 'clients.edit'], false],
            'employee' => ['موظف', 'الاطلاع على الفواتير والعملاء', ['invoices.view', 'clients.view'], false],
        ];
        foreach ($roles as $slug => [$name, $description, $permissions, $locked]) {
            $role = Role::firstOrCreate(['slug' => $slug], ['name' => $name, 'description' => $description, 'built_in' => true, 'locked' => $locked]);
            foreach ($permissions as $permission) $role->permissions()->firstOrCreate(['permission' => $permission]);
        }
    }
}
