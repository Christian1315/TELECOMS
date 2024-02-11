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
        Schema::create('definitif_s_ms', function (Blueprint $table) {
            $table->id();
            $table->foreignId("sender")
                ->nullable()
                ->constrained("users", "id")
                ->onUpdate("CASCADE")
                ->onDelete("CASCADE");
            $table->longText('message');
            $table->string('expeditor');
            $table->string('destinataire');
            $table->integer('sms_count');
            $table->string('amount');
            $table->string('sms_num');

            $table->boolean('sended')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('definitif_s_ms');
    }
};
