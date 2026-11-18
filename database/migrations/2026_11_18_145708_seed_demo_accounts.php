<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Demo accounts shared with DatabaseSeeder. They are created here so a
     * production deployment has accounts to sign in with without running the
     * seeder, which also creates sample visits and stays development-only.
     *
     * @var array<int, array{name: string, email: string, role: string, contact_number: string}>
     */
    private const ACCOUNTS = [
        ['name' => 'Campus Admin', 'email' => 'admin@qrvisits.test', 'role' => 'admin', 'contact_number' => '09170000001'],
        ['name' => 'Gate Guard', 'email' => 'guard@qrvisits.test', 'role' => 'guard', 'contact_number' => '09170000002'],
        ['name' => 'Maria Santos', 'email' => 'visitor@qrvisits.test', 'role' => 'visitor', 'contact_number' => '09170000003'],
    ];

    public function up(): void
    {
        $now = now();

        $existingEmails = DB::table('users')
            ->whereIn('email', array_column(self::ACCOUNTS, 'email'))
            ->pluck('email')
            ->all();

        $accounts = [];

        foreach (self::ACCOUNTS as $account) {
            if (in_array($account['email'], $existingEmails, true)) {
                continue;
            }

            $accounts[] = [
                'id' => (string) Str::uuid(),
                'name' => $account['name'],
                'email' => $account['email'],
                'role' => $account['role'],
                'contact_number' => $account['contact_number'],
                'email_verified_at' => $now,
                'password' => Hash::make('password'),
                'two_factor_secret' => null,
                'two_factor_recovery_codes' => null,
                'two_factor_confirmed_at' => null,
                'remember_token' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if ($accounts !== []) {
            DB::table('users')->insert($accounts);
        }
    }

    /**
     * The accounts are left in place: they may own visits, and rolling back
     * must not delete data a real user has since created. Re-running up() is
     * idempotent, so a rollback followed by a migrate is still safe.
     */
    public function down(): void
    {
        //
    }
};
