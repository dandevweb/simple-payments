<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{
    private static string $table = 'transfers';
    private static string $constrained = 'wallets';

    public function up(): void
    {
        Schema::create(self::$table, static function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_wallet_id')->constrained(self::$constrained);
            $table->foreignId('to_wallet_id')->constrained(self::$constrained);
            $table->decimal('value', 10);
            $table->timestamp('transferred_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(self::$table);
    }
};
