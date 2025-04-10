<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private static string $table = 'wallets';

    public function up(): void
    {
        Schema::create(self::$table, static function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('balance', 10, 2)->default(0.00);
            $table->timestamps();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists(self::$table);
    }
};
