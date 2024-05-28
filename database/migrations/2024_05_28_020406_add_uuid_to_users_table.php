<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->uuid('uuid')->nullable(true)->unique('users_uuid_unique');
        });

        User::query()
            ->whereNull('uuid')
            ->get()
            ->each(function (User $user) {
                $user->uuid = Str::uuid();
                $user->save();
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_uuid_unique');
            $table->dropColumn('uuid');
        });
    }
};
