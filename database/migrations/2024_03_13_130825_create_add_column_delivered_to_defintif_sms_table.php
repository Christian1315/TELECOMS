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
        Schema::table('definitif_s_ms', function (Blueprint $table) {
            $table->foreignId("sms_attached")
                ->nullable()
                ->constrained("sms", "id")
                ->onUpdate("CASCADE")
                ->onDelete("CASCADE");
            $table->boolean('delivered')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('definitif_s_ms', function (Blueprint $table) {
            //
        });
    }
};
