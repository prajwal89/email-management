<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->boolean('is_subscribed_for_emails')->after('provider_id')->default(1);
        });

        Schema::create('em_email_events', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_enabled')->default(1);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('em_email_logs', function (Blueprint $table): void {
            $table->id();
            $table->string('message_id')->nullable();
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
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('clicked_at')->nullable();
            $table->timestamps();

            $table->index(['eventable_id', 'eventable_type']);
            $table->index(['receivable_id', 'receivable_type']);
        });

        Schema::create('em_email_visits', function (Blueprint $table): void {
            $table->id();
            $table->text('path');
            $table->string('session_id', 64);
            $table->string('ip');
            $table->string('message_id')->nullable();
            $table->timestamps();
        });

        Schema::create('em_email_campaign', function (Blueprint $table): void {
            $table->id();

            $table->string('name', 255);
            $table->string('slug', 255)->unique();
            $table->text('description')->nullable();

            $table->json('receivable_groups')->nullable();
            $table->string('batch_id')->nullable();
            $table->timestamp('started_on')->nullable();
            $table->timestamp('ended_on')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });


        Schema::create('em_newsletter_emails', function (Blueprint $table): void {
            $table->id();
            $table->string('email')->unique();
            $table->datetime('email_verified_at')->nullable();
            $table->datetime('unsubscribed_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('em_cold_emails', function (Blueprint $table): void {
            $table->id();
            $table->string('email');
            $table->string('collection_reason')->nullable();
            $table->string('collected_from')->nullable();
            $table->json('data')->nullable();
            $table->datetime('unsubscribed_at')->nullable();
            $table->timestamps();
            $table->unique('email');
        });


        Schema::create('em_newsletter_emails', function (Blueprint $table): void {
            $table->id();
            $table->string('email')->unique();
            $table->datetime('email_verified_at')->nullable();
            $table->datetime('unsubscribed_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });


        Schema::create('email_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('email_id')->constrained()->onDelete('cascade');
            $table->string('email');
            $table->enum('type', ['to', 'cc', 'bcc']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('em_email_events');
    }
};
