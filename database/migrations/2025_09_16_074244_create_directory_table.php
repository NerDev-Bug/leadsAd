<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('directory', function (Blueprint $table) {
            $table->id();
            $table->string('area');
            $table->string('region');
            $table->string('place');
            $table->string('business_name');
            $table->string('address')->nullable();
            $table->string('contact_name');
            $table->string('contact_no');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('directory');
    }
};
