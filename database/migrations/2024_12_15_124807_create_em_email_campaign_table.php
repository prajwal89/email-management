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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('em_email_campaign');
    }
};
