<?php

namespace Tests\Feature;

use Tests\TestCase;

class RegistrationFeedbackTest extends TestCase
{
    public function test_login_displays_success_after_registration(): void
    {
        $this->withSession(['success' => 'Pendaftaran berhasil. Silakan masuk.'])
            ->get('/login')->assertOk()->assertSee('Pendaftaran berhasil. Silakan masuk.');
    }
}
