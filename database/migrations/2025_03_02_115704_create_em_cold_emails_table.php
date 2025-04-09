<?php

declare(strict_types=1);

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
        Schema::create('em_cold_emails', function (Blueprint $table): void {
            $table->id();
            $table->string('email');
            $table->string('collection_reason')->nullable();
            $table->string('collected_from')->nullable();
            $table->json('data')->nullable();
            $table->datetime('unsubscribed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('em_cold_emails');
    }
};
