<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visits', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('visitor_name');
            $table->string('visitor_contact', 30);
            $table->string('purpose');
            $table->string('host_name');
            $table->date('visit_date')->index();
            $table->time('expected_arrival')->nullable();
            $table->string('status')->default('pending')->index();
            $table->string('pass_code', 12)->nullable()->unique();
            $table->text('denial_reason')->nullable();
            $table->boolean('is_walk_in')->default(false);
            $table->timestamp('approved_at')->nullable();
            $table->foreignUuid('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('time_in')->nullable();
            $table->timestamp('time_out')->nullable();
            $table->foreignUuid('checked_in_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('checked_out_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visits');
    }
};
