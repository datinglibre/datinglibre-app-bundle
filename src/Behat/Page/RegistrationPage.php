<?php

declare(strict_types=1);

namespace DatingLibre\AppBundle\Behat\Page;

use FriendsOfBehat\PageObjectExtension\Page\SymfonyPage;

class RegistrationPage extends SymfonyPage
{
    public function getRouteName(): string
    {
        return 'user_register';
    }

    public function fillInDetails(string $email): void
    {
        $this->getElement('email')->setValue($email);
        $this->getElement('password')->setValue("password");
        $this->getElement('agreeTerms')->setValue(true);
        $this->getElement('register')->click();
    }

    protected function getDefinedElements(): array
    {
        return array_merge(parent::getDefinedElements(), [
            'email' => '#registration_form_email',
            'password' => '#registration_form_password',
            'agreeTerms' => '#registration_form_agreeTerms',
            'register' => '#register'
        ]);
    }
}
