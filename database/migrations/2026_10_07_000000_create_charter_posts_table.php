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
        // What is posted on the Bulletin, Events and Others sheets of the
        // Citizen's Charter book (public/charter).
        Schema::create('charter_posts', function (Blueprint $table) {
            $table->id();
            // the sheet it is posted on: bulletin, events or others
            $table->string('sheet', 20)->index();
            $table->string('title');
            // the date as it is to be shown ("28 November 2026"); may be left out
            $table->string('date_text')->nullable();
            $table->text('details')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('charter_posts');
    }
};
