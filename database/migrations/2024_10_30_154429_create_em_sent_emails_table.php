<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('em_sent_emails', function (Blueprint $table): void {
            $table->id();
            $table->text('subject');
            $table->unsignedBigInteger('receivable_id')->nullable();
            $table->string('receivable_type')->nullable();
            $table->unsignedBigInteger('eventable_id')->nullable();
            $table->string('eventable_type')->nullable();
            $table->json('context')->nullable();
            $table->json('headers')->nullable();
            $table->string('sender_email')->nullable();
            $table->string('recipient_email')->nullable();
            $table->text('email_content')->nullable();
            $table->string('hash', 32);
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('clicked_at')->nullable();
            $table->timestamps();

            $table->index(['eventable_id', 'eventable_type']);
            $table->index(['receivable_id', 'receivable_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('em_sent_emails');
    }
};
