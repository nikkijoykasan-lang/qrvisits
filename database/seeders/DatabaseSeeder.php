<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Role;
use App\Enums\VisitStatus;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

final class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = $this->demoUser('Campus Admin', 'admin@qrvisits.test', Role::Admin, '09170000001');
        $guard = $this->demoUser('Gate Guard', 'guard@qrvisits.test', Role::Guard, '09170000002');
        $visitor = $this->demoUser('Maria Santos', 'visitor@qrvisits.test', Role::Visitor, '09170000003');

        if (Visit::query()->exists()) {
            return;
        }

        Visit::factory()->approved()->create([
            'user_id' => $visitor->id,
            'visitor_name' => $visitor->name,
            'visitor_contact' => $visitor->contact_number,
            'purpose' => 'Parent meeting',
            'host_name' => 'Registrar Office',
            'visit_date' => now()->toDateString(),
            'approved_by' => $admin->id,
        ]);

        Visit::factory()->create([
            'user_id' => $visitor->id,
            'visitor_name' => $visitor->name,
            'visitor_contact' => $visitor->contact_number,
            'purpose' => 'Scholarship inquiry',
            'host_name' => 'Dean of Students',
            'visit_date' => now()->addDay()->toDateString(),
        ]);

        Visit::factory()->completed()->create([
            'user_id' => $visitor->id,
            'visitor_name' => $visitor->name,
            'visitor_contact' => $visitor->contact_number,
            'purpose' => 'Document request',
            'host_name' => 'Accounting Office',
            'visit_date' => now()->subDay()->toDateString(),
            'approved_by' => $admin->id,
            'checked_in_by' => $guard->id,
            'checked_out_by' => $guard->id,
            'time_in' => now()->subDay()->setTime(9, 10),
            'time_out' => now()->subDay()->setTime(10, 5),
        ]);

        Visit::factory()->denied()->create([
            'user_id' => $visitor->id,
            'visitor_name' => $visitor->name,
            'visitor_contact' => $visitor->contact_number,
            'purpose' => 'Vendor presentation',
            'host_name' => 'Purchasing Office',
            'visit_date' => now()->addDays(2)->toDateString(),
            'approved_by' => $admin->id,
        ]);

        Visit::factory()->walkIn()->create([
            'visitor_name' => 'Pedro Reyes',
            'visitor_contact' => '09181110001',
            'purpose' => 'Delivery of school supplies',
            'host_name' => 'Supply Office',
            'visit_date' => now()->toDateString(),
        ]);

        foreach ([
            ['name' => 'Ana dela Cruz', 'purpose' => 'Enrollment inquiry', 'host' => 'Admissions Office', 'arrival' => '10:00'],
            ['name' => 'Ben Lim', 'purpose' => 'Supplier meeting', 'host' => 'Purchasing Office', 'arrival' => '10:20'],
        ] as $entry) {
            $insideVisitor = $this->demoUser(
                $entry['name'],
                Str::slug($entry['name']).'@qrvisits.test',
                Role::Visitor,
                '0918'.random_int(1000000, 9999999),
            );

            Visit::factory()->inside()->create([
                'user_id' => $insideVisitor->id,
                'visitor_name' => $insideVisitor->name,
                'visitor_contact' => $insideVisitor->contact_number,
                'purpose' => $entry['purpose'],
                'host_name' => $entry['host'],
                'visit_date' => now()->toDateString(),
                'expected_arrival' => $entry['arrival'],
                'approved_by' => $admin->id,
                'checked_in_by' => $guard->id,
                'time_in' => now()->subMinutes(35),
                'status' => VisitStatus::Inside,
            ]);
        }
    }

    /**
     * Demo accounts are kept free of two-factor authentication so they can be
     * used immediately during a presentation.
     */
    private function demoUser(string $name, string $email, Role $role, string $contact): User
    {
        $user = User::query()->firstOrNew(['email' => $email]);

        $user->forceFill([
            'name' => $name,
            'role' => $role,
            'contact_number' => $contact,
            'email_verified_at' => $user->email_verified_at ?? now(),
            'password' => $user->password ?? Hash::make('password'),
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        return $user;
    }
}
