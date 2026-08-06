<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE products MODIFY dosage TEXT');
        DB::statement('ALTER TABLE products MODIFY target TEXT');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE products MODIFY dosage VARCHAR(255)');
        DB::statement('ALTER TABLE products MODIFY target VARCHAR(255)');
    }
};
