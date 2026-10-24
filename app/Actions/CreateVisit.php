<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\VisitStatus;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Throwable;

final readonly class CreateVisit
{
    /**
     * @param  array{purpose: string, host_name: string, visit_date: string, visitor_contact: string}  $attributes
     */
    public function handle(User $visitor, array $attributes, ?UploadedFile $idDocument = null): Visit
    {
        $idDocumentPath = $idDocument?->store('visitor-ids', 'local');

        try {
            return Visit::query()->create([
                'user_id' => $visitor->id,
                'visitor_name' => $visitor->name,
                'visitor_contact' => $attributes['visitor_contact'],
                'purpose' => $attributes['purpose'],
                'host_name' => $attributes['host_name'],
                'visit_date' => $attributes['visit_date'],
                'expected_arrival' => null,
                'status' => VisitStatus::Pending,
                'is_walk_in' => false,
                'id_document_path' => $idDocumentPath,
                'id_document_name' => $idDocument?->getClientOriginalName(),
            ]);
        } catch (Throwable $exception) {
            if ($idDocumentPath !== null) {
                Storage::disk('local')->delete($idDocumentPath);
            }

            throw $exception;
        }
    }
}
