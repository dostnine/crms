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
        Schema::table('charter_posts', function (Blueprint $table) {
            // the picture of a post on the Others sheet (a poster, say): where
            // it is kept on the local disk; most posts have none
            $table->string('image_path')->nullable()->after('details');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('charter_posts', function (Blueprint $table) {
            $table->dropColumn('image_path');
        });
    }
};
