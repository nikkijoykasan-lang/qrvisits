<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\VisitStatus;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Visit>
 */
final class VisitFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'visitor_name' => fake()->name(),
            'visitor_contact' => fake()->numerify('09#########'),
            'purpose' => fake()->randomElement(['Parent meeting', 'Document request', 'Supplier delivery', 'Job interview', 'Enrollment inquiry']),
            'host_name' => fake()->randomElement(['Registrar Office', 'Dean of Students', 'Accounting Office', 'IT Department']),
            'visit_date' => now()->toDateString(),
            'expected_arrival' => '09:00',
            'status' => VisitStatus::Pending,
            'pass_code' => null,
            'denial_reason' => null,
            'is_walk_in' => false,
            'approved_at' => null,
            'approved_by' => null,
            'time_in' => null,
            'time_out' => null,
        ];
    }

    public function approved(): self
    {
        return $this->state(fn (array $attributes): array => [
            'status' => VisitStatus::Approved,
            'pass_code' => Str::upper(Str::random(8)),
            'approved_at' => now(),
        ]);
    }

    public function inside(): self
    {
        return $this->state(fn (array $attributes): array => [
            'status' => VisitStatus::Inside,
            'pass_code' => Str::upper(Str::random(8)),
            'approved_at' => now(),
            'time_in' => now()->subHour(),
        ]);
    }

    public function completed(): self
    {
        return $this->state(fn (array $attributes): array => [
            'status' => VisitStatus::Completed,
            'pass_code' => Str::upper(Str::random(8)),
            'approved_at' => now(),
            'time_in' => now()->subHours(3),
            'time_out' => now()->subHour(),
        ]);
    }

    public function denied(): self
    {
        return $this->state(fn (array $attributes): array => [
            'status' => VisitStatus::Denied,
            'denial_reason' => 'Incomplete details submitted.',
        ]);
    }

    public function walkIn(): self
    {
        return $this->state(fn (array $attributes): array => [
            'user_id' => null,
            'is_walk_in' => true,
        ]);
    }
}
