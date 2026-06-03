<?php

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * @internal
 */
final class AuthLoginTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    public function testInvalidLoginReturnsFriendlyMessageWhenDatabaseLookupFails(): void
    {
        $result = $this->post('login', [
            'email' => 'not-real@example.com',
            'password' => 'wrong-password',
        ]);

        $result->assertRedirectTo('login');
        $result->assertSessionHas('error', 'Sistem login sedang bermasalah. Coba lagi nanti.');
    }
}
