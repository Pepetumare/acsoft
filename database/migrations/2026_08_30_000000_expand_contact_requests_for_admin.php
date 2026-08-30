<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contact_requests', function (Blueprint $table) {
            $table->string('email')->nullable()->after('name');
            $table->string('phone', 50)->nullable()->after('email');
            $table->string('type', 30)->default('contacto')->after('message');
            $table->index(['status', 'created_at']);
            $table->index(['type', 'created_at']);
        });

        DB::table('contact_requests')
            ->orderBy('id')
            ->each(function (object $request): void {
                $contact = trim((string) $request->contact);

                DB::table('contact_requests')
                    ->where('id', $request->id)
                    ->update([
                        'email' => filter_var($contact, FILTER_VALIDATE_EMAIL)
                            ? $contact
                            : null,
                        'phone' => filter_var($contact, FILTER_VALIDATE_EMAIL)
                            ? null
                            : ($contact ?: null),
                        'status' => $request->status === 'pending'
                            ? 'pendiente'
                            : $request->status,
                    ]);
            });
    }

    public function down(): void
    {
        Schema::table('contact_requests', function (Blueprint $table) {
            $table->dropIndex(['status', 'created_at']);
            $table->dropIndex(['type', 'created_at']);
            $table->dropColumn(['email', 'phone', 'type']);
        });
    }
};
