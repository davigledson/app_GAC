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
         Schema::table('activities', function (Blueprint $table) {
            $table->string('request_complementary_hours');
            $table->string('valid_complementary_hours')->nullable();
            $table->timestamp('occurrence_data');
        });

         Schema::table('users', function (Blueprint $table) {
            $table->string('registration')->nullable(); //matricula
            $table->string('initiation_period')->nullable(); //periodo de iniciação 2022.1
            $table->string('course')->nullable();
            $table->string('paid_complementary_hours')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->dropColumn('request_complementary_hours');
            $table->dropColumn('valid_complementary_hours');
            $table->dropColumn('occurrence data');
        });
    }
};
