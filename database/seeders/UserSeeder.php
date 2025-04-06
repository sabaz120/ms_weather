<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                "email" => "admin@pulpoline.com",
                "password" => "123789a1A1",
                "name" => "Admin pulpoline",
                "role" => "admin",
            ]
        ];
        foreach ($users as $user) {
            $model=\App\Models\User::firstOrCreate([
                'email' => $user['email'],
            ], [
                'password' => bcrypt($user['password']),
                'name' => $user['name'],
            ]);
            $model->assignRole($user['role']);
        }
    }
}
