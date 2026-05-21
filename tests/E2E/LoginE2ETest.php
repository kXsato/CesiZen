<?php

namespace App\Tests\E2E;

use Symfony\Component\Panther\PantherTestCase;

class LoginE2ETest extends PantherTestCase
{
    public function testLoginPageDisplaysForm(): void
    {
        $client = static::createPantherClient();
        $client->request('GET', '/login');

        $client->waitFor('input[name="_username"]', 5);

        self::assertSelectorExists('input[name="_username"]');
        self::assertSelectorExists('input[name="_password"]');
        self::assertSelectorExists('button[type="submit"]');
    }

    public function testInvalidCredentialsShowError(): void
    {
        $client = static::createPantherClient();
        $crawler = $client->request('GET', '/login');

        $client->waitFor('input[name="_username"]', 5);
        $crawler = $client->refreshCrawler();

        $form = $crawler->selectButton('Sign in')->form([
            '_username' => 'nobody@example.com',
            '_password' => 'wrong-password',
        ]);
        $client->submit($form);

        $client->waitFor('.alert-danger', 5);
        self::assertSelectorTextContains('.alert-danger', 'Invalid credentials.');
    }
}
