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
        Schema::create('campagnes', function (Blueprint $table) {
            $table->id();
            $table->text("name");

            $table->text("start_date");
            $table->text("end_date");
            $table->text("sms_send_frequency");

            $table->integer("num_time_by_day");

            $table->foreignId("expeditor")
                ->nullable()
                ->constrained("expeditors", "id")
                ->onUpdate("CASCADE")
                ->onDelete("CASCADE");

            $table->boolean("initiated")->default(false);
            $table->text("sms_type")->nullable();

            $table->foreignId("status")
                ->nullable()
                ->constrained("campagne_statuses", "id")
                ->onUpdate("CASCADE")
                ->onDelete("CASCADE");


            $table->foreignId("owner")
                ->nullable()
                ->constrained("users", "id")
                ->onUpdate("CASCADE")
                ->onDelete("CASCADE");
            $table->longText("message");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campagnes');
    }
};
