<?php

namespace Tests\Feature;

use Tests\TestCase;

class PaymentConfigurationTest extends TestCase
{
    public function test_bank_destination_is_explicit_environment_configuration(): void
    {
        $previous = [];
        foreach (['TRAVEL_BANK_NAME' => 'Bank QA', 'TRAVEL_BANK_ACCOUNT' => 'DEMO-DO-NOT-TRANSFER', 'TRAVEL_BANK_HOLDER' => 'Travel QA', 'TRAVEL_QRIS_IMAGE' => '/storage/payments/official.png'] as $key => $value) {
            $previous[$key] = [$_ENV[$key] ?? null, $_SERVER[$key] ?? null];
            $_ENV[$key] = $_SERVER[$key] = $value;
            putenv($key.'='.$value);
        }
        try {
            $path = config_path('travel.php');
            $this->assertFileExists($path);
            $settings = require $path;
            $this->assertSame('Bank QA', $settings['bank_name']);
            $this->assertSame('DEMO-DO-NOT-TRANSFER', $settings['bank_account']);
            $this->assertSame('Travel QA', $settings['bank_holder']);
            $this->assertSame('/storage/payments/official.png', $settings['qris_image']);
        } finally {
            foreach ($previous as $key => [$env, $server]) {
                if ($env === null) { unset($_ENV[$key]); } else { $_ENV[$key] = $env; }
                if ($server === null) { unset($_SERVER[$key]); } else { $_SERVER[$key] = $server; }
                putenv($key);
            }
        }
    }
}
