<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\VisitStatus;
use Carbon\CarbonInterface;
use Database\Factories\VisitFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read string $id
 * @property-read string|null $user_id
 * @property-read string $visitor_name
 * @property-read string $visitor_contact
 * @property-read string $purpose
 * @property-read string $host_name
 * @property-read CarbonInterface $visit_date
 * @property-read string|null $expected_arrival
 * @property-read VisitStatus $status
 * @property-read string|null $pass_code
 * @property-read string|null $denial_reason
 * @property-read bool $is_walk_in
 * @property-read CarbonInterface|null $approved_at
 * @property-read string|null $approved_by
 * @property-read CarbonInterface|null $time_in
 * @property-read CarbonInterface|null $time_out
 * @property-read string|null $checked_in_by
 * @property-read string|null $checked_out_by
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 * @property-read User|null $visitor
 * @property-read User|null $approver
 */
final class Visit extends Model
{
    /** @use HasFactory<VisitFactory> */
    use HasFactory;

    use HasUuids;

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'id' => 'string',
            'user_id' => 'string',
            'visitor_name' => 'string',
            'visitor_contact' => 'string',
            'purpose' => 'string',
            'host_name' => 'string',
            'visit_date' => 'date',
            'expected_arrival' => 'string',
            'status' => VisitStatus::class,
            'pass_code' => 'string',
            'denial_reason' => 'string',
            'is_walk_in' => 'boolean',
            'approved_at' => 'datetime',
            'approved_by' => 'string',
            'time_in' => 'datetime',
            'time_out' => 'datetime',
            'checked_in_by' => 'string',
            'checked_out_by' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, covariant $this>
     */
    public function visitor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return BelongsTo<User, covariant $this>
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * @return BelongsTo<User, covariant $this>
     */
    public function checkedInBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_in_by');
    }

    /**
     * @return BelongsTo<User, covariant $this>
     */
    public function checkedOutBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_out_by');
    }

    /**
     * A pass may only be scanned on the day it was approved for.
     */
    public function isUsableOn(CarbonInterface $date): bool
    {
        return $this->status === VisitStatus::Approved
            && $this->visit_date->isSameDay($date);
    }

    public function isInside(): bool
    {
        return $this->status === VisitStatus::Inside;
    }

    /**
     * @param  Builder<Visit>  $query
     */
    public function scopeOnDate(Builder $query, CarbonInterface $date): void
    {
        $query->whereDate('visit_date', $date->toDateString());
    }

    /**
     * @param  Builder<Visit>  $query
     */
    public function scopePending(Builder $query): void
    {
        $query->where('status', VisitStatus::Pending->value);
    }

    /**
     * @param  Builder<Visit>  $query
     */
    public function scopeApproved(Builder $query): void
    {
        $query->where('status', VisitStatus::Approved->value);
    }

    /**
     * @param  Builder<Visit>  $query
     */
    public function scopeInside(Builder $query): void
    {
        $query->where('status', VisitStatus::Inside->value);
    }

    /**
     * @param  Builder<Visit>  $query
     */
    public function scopeCompleted(Builder $query): void
    {
        $query->where('status', VisitStatus::Completed->value);
    }
}
