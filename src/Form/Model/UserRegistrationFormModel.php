<?php

namespace App\Form\Model;

use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\UniqueUser;

class UserRegistrationFormModel
{
    /**
     * @Assert\NotBlank()
     * @Assert\Email()
     * @UniqueUser()
     */
    public $email;
    
    public $firstName;

    /**
     * @Assert\NotBlank(message="Пароль не может быть пустым")
     * @Assert\Length(min="6", minMessage="Пароль должен содержать минимум 6 символов")
     */
    public $plainPassword;
}
