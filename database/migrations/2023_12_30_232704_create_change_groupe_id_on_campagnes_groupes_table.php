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
        Schema::table('campagnes_groupes', function (Blueprint $table) {
            $table->foreignId("groupe_id_new")->nullable()->constrained("groupes", "id");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campagnes_groupes', function (Blueprint $table) {
            //
        });
    }
};
