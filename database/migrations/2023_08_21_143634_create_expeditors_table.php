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
        Schema::create('expeditors', function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->foreignId("status")
                ->nullable()
                ->constrained("expeditor_statuses", "id")
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
        Schema::dropIfExists('expeditors');
    }
};
