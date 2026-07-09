<?php

namespace App\Controller;

use App\Entity\User;

trait GetsCurrentUserTrait
{
    protected function getCurrentUser(): User
    {
        $user = $this->getUser();
        \assert($user instanceof User);

        return $user;
    }
}
