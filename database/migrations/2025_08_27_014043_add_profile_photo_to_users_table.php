<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // Added this import for DB facade

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('profile_photo')->nullable()->after('id');
        });
        
        // Update existing users to have 'tamu' role if they don't have a role set
        DB::table('users')->whereNull('role')->orWhere('role', 'non-admin')->update(['role' => 'tamu']);
        
        // Change the default value for the role column
        DB::statement("ALTER TABLE users ALTER COLUMN role SET DEFAULT 'tamu'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('profile_photo');
        });
        
        // Revert role default back to 'non-admin'
        DB::statement("ALTER TABLE users ALTER COLUMN role SET DEFAULT 'non-admin'");
    }
};
