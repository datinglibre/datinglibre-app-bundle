<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Controller;

use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UserLogoutController extends AbstractController
{
    public function matches()
    {
        throw new Exception('Specify logout in auth configuration');
    }
}
