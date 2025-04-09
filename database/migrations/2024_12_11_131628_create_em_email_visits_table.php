<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('em_email_visits', function (Blueprint $table): void {
            $table->id();
            $table->text('path');
            $table->string('session_id', 64);
            $table->string('ip');
            $table->string('email_hash', 32)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('em_email_visits');
    }
};
