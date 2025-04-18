<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private static string $table = 'transfer_logs';

    public function up(): void
    {
        Schema::create(self::$table, static function (Blueprint $table) {
            $table->id();
            $table->foreignId('payer_id')->nullable();
            $table->foreignId('payee_id')->nullable();
            $table->foreignId('transfer_id')->nullable()->constrained('transfers')->cascadeOnDelete();
            $table->decimal('value', 10);
            $table->enum('status', ['success', 'fail', 'pending']);
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(self::$table);
    }
};
