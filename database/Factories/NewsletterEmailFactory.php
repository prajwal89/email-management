<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Prajwal89\EmailManagement\Models\NewsletterEmail;

class NewsletterEmailFactory extends Factory
{
    protected $model = NewsletterEmail::class;

    public function definition(): array
    {
        return [
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'unsubscribed_at' => null,
        ];
    }
}
