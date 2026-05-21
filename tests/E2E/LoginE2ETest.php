<?php

namespace App\Tests\E2E;

use Symfony\Component\Panther\PantherTestCase;

class LoginE2ETest extends PantherTestCase
{
    public function testLoginPageDisplaysForm(): void
    {
        $client = static::createPantherClient();
        $client->request('GET', '/login');

        self::assertSelectorExists('input[name="_username"]');
        self::assertSelectorExists('input[name="_password"]');
        self::assertSelectorExists('button[type="submit"]');
    }

    public function testInvalidCredentialsShowError(): void
    {
        $client = static::createPantherClient();
        $crawler = $client->request('GET', '/login');

        $form = $crawler->selectButton('Sign in')->form([
            '_username' => 'nobody@example.com',
            '_password' => 'wrong-password',
        ]);
        $client->submit($form);

        $client->waitFor('.alert-danger');
        self::assertSelectorTextContains('.alert-danger', 'Invalid credentials.');
    }
}
