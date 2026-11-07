<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

#[Signature('app:ensure-admin {--name=} {--email=} {--password=}')]
#[Description('Create or update an administrator account')]
class EnsureAdmin extends Command
{
    public function handle(): int
    {
        $name = (string) ($this->option('name') ?: getenv('ADMIN_NAME'));
        $email = (string) ($this->option('email') ?: getenv('ADMIN_EMAIL'));
        $password = (string) ($this->option('password') ?: getenv('ADMIN_PASSWORD'));

        $validator = Validator::make([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ], [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:12'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $admin = User::query()->withTrashed()->firstOrNew(['email' => $email]);
        $admin->forceFill([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'role' => 'admin',
            'deleted_at' => null,
        ])->save();

        if (! $admin->isAdmin() || ! Hash::check($password, $admin->password)) {
            $this->error('Akun admin gagal diverifikasi.');

            return self::FAILURE;
        }

        $this->info("Admin {$email} siap digunakan.");

        return self::SUCCESS;
    }
}
