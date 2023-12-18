<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('currency', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 10);
            $table->float('to_rub')->default(0);
        });

        DB::table('currency')->insert([
            [
                'slug' => 'euro',
                'to_rub' => 150.0
            ],
            [
                'slug' => 'usd',
                'to_rub' => 100.0
            ],
            [
                'slug' => 'rub',
                'to_rub' => 0
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('currency_');
    }
};
