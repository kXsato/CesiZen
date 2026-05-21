<?php

namespace App\Tests;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class LoginControllerTest extends WebTestCase
{
    private const USER_EMAIL = 'email@example.com';
    private const SIGN_IN_BUTTON = 'Sign in';
    private const LOGIN_ROUTE = '/login';
    private const ALERT_DANGER = '.alert-danger';

    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $container = static::getContainer();
        $em = $container->get('doctrine.orm.entity_manager');
        $userRepository = $em->getRepository(User::class);

        // Remove any existing users from the test database
        foreach ($userRepository->findAll() as $user) {
            $em->remove($user);
        }

        $em->flush();

        // Create a User fixture
        /** @var UserPasswordHasherInterface $passwordHasher */
        $passwordHasher = $container->get('security.user_password_hasher');

        $user = (new User())->setEmail(self::USER_EMAIL);
        $user->setUserName('testuser');
        $user->setBirthDate(new \DateTime('1990-01-01'));
        $user->setPassword($passwordHasher->hashPassword($user, 'password'));

        $em->persist($user);
        $em->flush();
    }

    public function testLogin(): void
    {
        // Denied - Can't login with invalid email address.
        $this->client->request('GET', self::LOGIN_ROUTE);
        self::assertResponseIsSuccessful();

        $this->client->submitForm(self::SIGN_IN_BUTTON, [
            '_username' => 'doesNotExist@example.com',
            '_password' => 'password',
        ]);

        self::assertResponseRedirects(self::LOGIN_ROUTE);
        $this->client->followRedirect();

        // Ensure we do not reveal if the user exists or not.
        self::assertSelectorTextContains(self::ALERT_DANGER, 'Invalid credentials.');

        // Denied - Can't login with invalid password.
        $this->client->request('GET', self::LOGIN_ROUTE);
        self::assertResponseIsSuccessful();

        $this->client->submitForm(self::SIGN_IN_BUTTON, [
            '_username' => self::USER_EMAIL,
            '_password' => 'bad-password',
        ]);

        self::assertResponseRedirects(self::LOGIN_ROUTE);
        $this->client->followRedirect();

        // Ensure we do not reveal the user exists but the password is wrong.
        self::assertSelectorTextContains(self::ALERT_DANGER, 'Invalid credentials.');

        // Success - Login with valid credentials is allowed.
        $this->client->submitForm(self::SIGN_IN_BUTTON, [
            '_username' => self::USER_EMAIL,
            '_password' => 'password',
        ]);

        self::assertResponseRedirects('/');
        $this->client->followRedirect();

        self::assertSelectorNotExists(self::ALERT_DANGER);
    }
}
