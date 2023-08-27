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
        Schema::create('sms', function (Blueprint $table) {
            $table->id();
            $table->string('messageId');
            $table->string('from');
            $table->string('to');
            $table->longText('message');
            $table->string('type');
            $table->string('route');
            $table->string('sms_count');
            $table->string('amount');
            $table->integer('sms_num')->nullable();
            $table->string('currency');
            $table->foreignId("status")
                ->nullable()
                ->constrained("sms_statuses", "id")
                ->onUpdate("CASCADE")
                ->onDelete("CASCADE");
            $table->foreignId("owner")
                ->nullable()
                ->constrained("users", "id")
                ->onUpdate("CASCADE")
                ->onDelete("CASCADE");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sms');
    }
};
