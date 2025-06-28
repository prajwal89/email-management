<?php

declare(strict_types=1);

namespace Prajwal89\EmailManagement\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Prajwal89\EmailManagement\Models\EmailLog;

class EmailLogFactory extends Factory
{
    protected $model = EmailLog::class;

    public function definition()
    {
        return [
            'message_id' => $this->faker->uuid,
            'from' => $this->faker->email,
            'mailer' => 'smtp',
            'transport' => 'smtp',
            'subject' => $this->faker->sentence,
            'sent_at' => now(),
        ];
    }
}
