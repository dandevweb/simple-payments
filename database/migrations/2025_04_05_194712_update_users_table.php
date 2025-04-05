<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private static string $table = 'users';

    public function up(): void
    {
        Schema::table(self::$table, static function (Blueprint $table) {
            $table->string('document', 14)->unique();
            $table->enum('type', ['common', 'merchant'])->default('common');
        });
    }

    public function down(): void
    {
        Schema::table(self::$table, static function (Blueprint $table) {
            $table->dropColumn('cpf');
            $table->dropColumn('type');
        });
    }
};
