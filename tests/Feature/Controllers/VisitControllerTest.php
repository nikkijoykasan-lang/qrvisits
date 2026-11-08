<?php

declare(strict_types=1);

use App\Enums\VisitStatus;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('shows the visit request form to a visitor', function (): void {
    $visitor = User::factory()->create();

    $this->actingAs($visitor)
        ->get(route('visits.create'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('visit/Create')
            ->where('defaults.visit_date', now()->toDateString()));
});

it('hides the visit request form from staff', function (): void {
    $guard = User::factory()->guard()->create();

    $this->actingAs($guard)
        ->get(route('visits.create'))
        ->assertNotFound();
});

it('lets a visitor request a visit', function (): void {
    Storage::fake('local');

    $visitor = User::factory()->create(['contact_number' => '09171234567']);

    $response = $this->actingAs($visitor)->post(route('visits.store'), [
        'purpose' => 'Parent meeting',
        'host_name' => 'Registrar Office',
        'visit_date' => now()->addDay()->toDateString(),
        'visitor_contact' => '09171234567',
        'expected_time_out' => '17:00',
        'id_document' => UploadedFile::fake()->image('school-id.jpg'),
    ]);

    $visit = Visit::query()->first();

    expect($visit)->not->toBeNull();

    $response->assertRedirectToRoute('visits.show', $visit);

    expect($visit->status)->toBe(VisitStatus::Pending)
        ->and($visit->user_id)->toBe($visitor->id)
        ->and($visit->visitor_name)->toBe($visitor->name)
        ->and($visit->pass_code)->toBeNull()
        ->and($visit->expected_arrival)->toBeNull()
        ->and($visit->expected_time_out)->toStartWith('17:00')
        ->and($visit->id_document_name)->toBe('school-id.jpg')
        ->and($visit->id_document_path)->not->toBeNull();

    Storage::disk('local')->assertExists((string) $visit->id_document_path);
});

it('requires a valid id document', function (): void {
    $visitor = User::factory()->create();

    $this->actingAs($visitor)->post(route('visits.store'), [
        'purpose' => 'Parent meeting',
        'host_name' => 'Registrar Office',
        'visit_date' => now()->toDateString(),
        'visitor_contact' => '09171234567',
        'expected_time_out' => '17:00',
    ])->assertSessionHasErrors('id_document');

    expect(Visit::query()->count())->toBe(0);
});

it('rejects an unsupported id document type', function (): void {
    $visitor = User::factory()->create();

    $this->actingAs($visitor)->post(route('visits.store'), [
        'purpose' => 'Parent meeting',
        'host_name' => 'Registrar Office',
        'visit_date' => now()->toDateString(),
        'visitor_contact' => '09171234567',
        'expected_time_out' => '17:00',
        'id_document' => UploadedFile::fake()->create('notes.txt', 10, 'text/plain'),
    ])->assertSessionHasErrors('id_document');

    expect(Visit::query()->count())->toBe(0);
});

it('rejects a visit date in the past', function (): void {
    $visitor = User::factory()->create();

    $response = $this->actingAs($visitor)->post(route('visits.store'), [
        'purpose' => 'Parent meeting',
        'host_name' => 'Registrar Office',
        'visit_date' => now()->subDay()->toDateString(),
        'visitor_contact' => '09171234567',
        'expected_time_out' => '17:00',
    ]);

    $response->assertSessionHasErrors('visit_date');

    expect(Visit::query()->count())->toBe(0);
});

it('lets a visitor see their pending request without a pass', function (): void {
    $visitor = User::factory()->create();
    $visit = Visit::factory()->create(['user_id' => $visitor->id]);

    $this->actingAs($visitor)
        ->get(route('visits.show', $visit))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('visit/Show')
            ->where('visit.status', VisitStatus::Pending->value)
            ->where('visit.pass_code', null));
});

it('hides another visitor request', function (): void {
    $visitor = User::factory()->create();
    $other = User::factory()->create();
    $visit = Visit::factory()->create(['user_id' => $other->id]);

    $this->actingAs($visitor)
        ->get(route('visits.show', $visit))
        ->assertNotFound();
});

it('lets a visitor cancel a pending request', function (): void {
    $visitor = User::factory()->create();
    $visit = Visit::factory()->create(['user_id' => $visitor->id]);

    $this->actingAs($visitor)
        ->delete(route('visits.destroy', $visit))
        ->assertRedirectToRoute('visits.index');

    expect(Visit::query()->find($visit->id))->toBeNull();
});

it('does not let a visitor cancel an approved request', function (): void {
    $visitor = User::factory()->create();
    $visit = Visit::factory()->approved()->create(['user_id' => $visitor->id]);

    $this->actingAs($visitor)
        ->delete(route('visits.destroy', $visit))
        ->assertNotFound();

    expect(Visit::query()->find($visit->id))->not->toBeNull();
});

it('blocks staff from creating visitor requests', function (): void {
    $guard = User::factory()->guard()->create();

    $this->actingAs($guard)
        ->post(route('visits.store'), [
            'purpose' => 'Test',
            'host_name' => 'Office',
            'visit_date' => now()->toDateString(),
            'visitor_contact' => '09171234567',
            'expected_time_out' => '17:00',
        ])
        ->assertNotFound();

    expect(Visit::query()->count())->toBe(0);
});

it('lets the owner and staff view an uploaded id', function (): void {
    Storage::fake('local');
    Storage::disk('local')->put('visitor-ids/id.jpg', 'fake-image-content');

    $visitor = User::factory()->create();
    $guard = User::factory()->guard()->create();

    $visit = Visit::factory()->create([
        'user_id' => $visitor->id,
        'id_document_path' => 'visitor-ids/id.jpg',
        'id_document_name' => 'id.jpg',
    ]);

    $this->actingAs($visitor)->get(route('visits.id-document', $visit))->assertOk();
    $this->actingAs($guard)->get(route('visits.id-document', $visit))->assertOk();
});

it('hides an uploaded id from other visitors', function (): void {
    Storage::fake('local');
    Storage::disk('local')->put('visitor-ids/id.jpg', 'fake-image-content');

    $visitor = User::factory()->create();
    $other = User::factory()->create();

    $visit = Visit::factory()->create([
        'user_id' => $visitor->id,
        'id_document_path' => 'visitor-ids/id.jpg',
        'id_document_name' => 'id.jpg',
    ]);

    $this->actingAs($other)->get(route('visits.id-document', $visit))->assertNotFound();
});

it('returns not found when a visit has no uploaded id', function (): void {
    $visitor = User::factory()->create();
    $visit = Visit::factory()->create(['user_id' => $visitor->id]);

    $this->actingAs($visitor)->get(route('visits.id-document', $visit))->assertNotFound();
});
