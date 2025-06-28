<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Prajwal89\EmailManagement\Enums\EmailContentType;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->boolean('is_subscribed_for_emails')->before('created_at')->default(1);
        });

        Schema::create('em_email_events', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_enabled')->default(1);
            $table->boolean('is_followup_email')->default(0);

            $table->foreignId('parent_event_id')
                ->nullable()
                ->constrained('em_email_events')
                ->restrictOnDelete();

            $table->timestamps();
        });

        Schema::create('em_email_campaigns', function (Blueprint $table): void {
            $table->id();

            $table->string('name', 255);
            $table->string('slug', 255)->unique();
            $table->text('description')->nullable();
            $table->boolean('is_enabled')->default(true);

            $table->timestamps();
        });

        Schema::create('em_follow_ups', function (Blueprint $table): void {
            $table->id();

            $table->unsignedBigInteger('followupable_id');
            $table->string('followupable_type');

            // actual email will be sent for the followupable
            $table->unsignedBigInteger('followup_email_event_id');

            $table->boolean('is_enabled')->default(true);

            $table->unsignedInteger('wait_for_days');

            $table->index(['followupable_id', 'followupable_type']);
            // $table->index(['sendable_id', 'sendable_type']);

            $table->timestamps();
        });

        Schema::create('em_email_campaign_runs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('email_campaign_id');
            $table->json('receivable_groups')->nullable();
            $table->string('batch_id')->index()->nullable();
            $table->timestamps();

            $table
                ->foreign('email_campaign_id')
                ->references('id')
                ->on('em_email_campaigns');
        });

        Schema::create('em_email_variants', function (Blueprint $table): void {
            $table->id();

            $table->string('name', 255);
            $table->string('slug', 255);

            $table->enum(
                column: 'content_type',
                allowed: collect(EmailContentType::cases())
                    ->map(fn($case) => $case->value)->toArray()
            )->default('markdown');

            $table->unsignedBigInteger('sendable_id')->nullable();
            $table->string('sendable_type')->nullable();

            $table->boolean('is_paused')->default(false);
            $table->boolean('is_winner')->default(false);
            $table->unsignedSmallInteger('exposure_percentage')->default(50);

            $table->timestamps();

            $table->unique(['sendable_type', 'sendable_id', 'slug']);
        });

        Schema::create('em_email_logs', function (Blueprint $table): void {
            $table->id();
            $table->string('message_id')->unique();
            $table->string('from');
            $table->string('mailer');
            $table->string('transport');
            $table->string('subject');

            $table->string('receivable_type')->nullable();
            $table->unsignedBigInteger('receivable_id')->nullable();

            // ! if sendable get deleted these columns may point to wrong log
            // as we are deleting using seeder file (only record of sendable)
            // same goes for receivable
            // ?should i use model observer to set these fields null on delete
            $table->string('sendable_type')->nullable();
            $table->unsignedBigInteger('sendable_id')->nullable();

            $table->unsignedBigInteger('email_variant_id')->nullable();

            $table->json('context')->nullable();
            $table->json('headers')->nullable();

            $table->text('html')->nullable();
            $table->text('text')->nullable();

            $table->unsignedInteger('opens')->default(0);
            $table->unsignedInteger('clicks')->default(0);

            $table->timestamp('sent_at')->nullable();
            // $table->timestamp('resent_at')->nullable();
            // $table->timestamp('accepted_at')->nullable();
            // $table->timestamp('delivered_at')->nullable();
            $table->timestamp('last_opened_at')->nullable();
            $table->timestamp('last_clicked_at')->nullable();
            $table->timestamp('complained_at')->nullable();

            $table->string('in_reply_to')->nullable();
            $table->timestamp('replied_at')->nullable();

            $table->timestamp('soft_bounced_at')->nullable();
            $table->timestamp('hard_bounced_at')->nullable();
            $table->timestamp('unsubscribed_at')->nullable();

            $table->string('bounce_code')->nullable();
            $table->text('bounce_reason')->nullable();

            $table->timestamps();

            $table->foreign('email_variant_id')->references('id')->on('em_email_variants');
            $table->index(['receivable_id', 'receivable_type']);
            $table->index(['sendable_id', 'sendable_type']);
        });

        Schema::create('em_email_visits', function (Blueprint $table): void {
            $table->id();
            $table->string('message_id')->nullable();
            $table->text('path');
            $table->string('session_id', 64)->nullable();
            $table->string('ip');
            $table->foreignIdFor(User::class)->nullable();
            $table->timestamps();

            $table->foreign('message_id')->references('message_id')->on('em_email_logs');
        });

        Schema::create('em_newsletter_emails', function (Blueprint $table): void {
            $table->id();
            $table->string('email')->unique();
            $table->datetime('email_verified_at')->nullable();
            $table->datetime('unsubscribed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('em_cold_emails', function (Blueprint $table): void {
            $table->id();
            $table->string('email')->unique();
            $table->string('collection_reason')->nullable();

            $table->unsignedBigInteger('collectable_id')->nullable();
            $table->string('collectable_type')->nullable();

            $table->string('collected_from')->nullable();

            $table->json('data')->nullable();

            $table->datetime('unsubscribed_at')->nullable();

            $table->timestamps();
        });

        Schema::create('em_recipients', function (Blueprint $table) {
            $table->id();
            $table->string('message_id');
            $table->string('email');
            $table->enum('type', ['to', 'cc', 'bcc']);
            $table->timestamps();

            $table->foreign('message_id')->references('message_id')->on('em_email_logs')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('em_recipients');
        Schema::dropIfExists('em_email_visits');
        Schema::dropIfExists('em_cold_emails');
        Schema::dropIfExists('em_newsletter_emails');
        Schema::dropIfExists('em_email_logs');
        Schema::dropIfExists('em_email_campaigns');
        Schema::dropIfExists('em_email_events');
        Schema::dropIfExists('em_follow_ups');
        Schema::dropIfExists('em_email_campaign_runs');

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('is_subscribed_for_emails');
        });
    }
};
