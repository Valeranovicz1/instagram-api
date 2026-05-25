<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'nome'     => 'Administrador do Sistema',
            'email'    => 'admin@instagram.com',
            'usuario'  => 'admin', 
            'password' => Hash::make('admin123'),
        ]);
    }
}