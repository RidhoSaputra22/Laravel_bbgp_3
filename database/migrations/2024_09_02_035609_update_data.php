<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $password = '12345';
        $hashedPassword = bcrypt($password);

        if (Schema::hasTable('users')) {
            DB::table('users')->update([
                'password' => $hashedPassword,
            ]);
        }

        if (Schema::hasTable('admins')) {
            DB::table('admins')->update([
                'password' => $hashedPassword,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
