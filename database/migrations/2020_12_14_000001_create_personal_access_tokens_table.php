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
    Schema::create('wheels', function (Blueprint $table) {
      $table->string('id')->primary();
      $table->string('name');
      $table->json('items');
      $table->timestamps();
    });

    Schema::create('settings', function (Blueprint $table) {
      $table->id();
      $table->string('background')->default('./img/background.jpg');
      $table->timestamps();
    });

    Schema::create('histories', function (Blueprint $table) {
      $table->id();
      $table->string('result');
      $table->timestamp('spun_at');
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
};
