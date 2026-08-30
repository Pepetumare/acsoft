<?php

use App\Enums\ContactRequestStatus;
use App\Enums\ContactRequestType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $validStatuses = array_column(ContactRequestStatus::cases(), 'value');
        $validTypes = array_column(ContactRequestType::cases(), 'value');

        DB::table('contact_requests')
            ->whereNull('status')
            ->orWhereNotIn('status', $validStatuses)
            ->update(['status' => ContactRequestStatus::Pending->value]);

        DB::table('contact_requests')
            ->whereNull('type')
            ->orWhereNotIn('type', $validTypes)
            ->update(['type' => ContactRequestType::Contact->value]);

        Schema::table('contact_requests', function (Blueprint $table) {
            $table->string('status')
                ->nullable()
                ->default(ContactRequestStatus::Pending->value)
                ->change();
            $table->string('type', 30)
                ->nullable()
                ->default(ContactRequestType::Contact->value)
                ->change();
        });
    }

    public function down(): void
    {
        DB::table('contact_requests')
            ->whereNull('status')
            ->update(['status' => 'pending']);
        DB::table('contact_requests')
            ->whereNull('type')
            ->update(['type' => ContactRequestType::Contact->value]);

        Schema::table('contact_requests', function (Blueprint $table) {
            $table->string('status')->default('pending')->nullable(false)->change();
            $table->string('type', 30)->default(ContactRequestType::Contact->value)->nullable(false)->change();
        });
    }
};
