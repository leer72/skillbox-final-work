<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Email;

class ProfileFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $emailConstraint = new Email(['message' => 'Email "{{ value }}" не действителен.',]);
        
        $builder
            ->add('email', EmailType::class, [
                'required' => false,
                'mapped' => false,
                'constraints' => $emailConstraint,
            ])
            ->add('firstName', null, [
                'required' => false,
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'Пароли должны совпадать',
                'required' => false,
                'mapped' => false,
            ])
        ;
    }
}
