<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Make email nullable
            $table->string('email')->nullable()->change();
        });

        // Update existing users with null nomor_registrasi to have a default value
        \App\Models\User::whereNull('nomor_registrasi')->update([
            'nomor_registrasi' => \Illuminate\Support\Str::random(10)
        ]);

        Schema::table('users', function (Blueprint $table) {
            // Make nomor_registrasi not nullable (assuming it already exists)
            // If it doesn't exist, we'll add it
            if (!Schema::hasColumn('users', 'nomor_registrasi')) {
                $table->string('nomor_registrasi')->after('email');
            } else {
                $table->string('nomor_registrasi')->nullable(false)->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Revert email to not nullable
            $table->string('email')->nullable(false)->change();
            
            // Make nomor_registrasi nullable again
            $table->string('nomor_registrasi')->nullable()->change();
        });
    }
};