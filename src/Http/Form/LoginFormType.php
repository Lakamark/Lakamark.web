<?php

namespace App\Http\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LoginFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class, [
                'required' => true,
                'attr' => [
                    'placeholder' => 'Username or Email',
                    'autofocus' => true,
                    'autocomplete' => true,
                ],
            ])
            ->add('password', PasswordType::class, [
                'required' => true,
                'attr' => [
                    'autocomplete' => 'current-password',
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Login',
            ]);
        parent::buildForm($builder, $options);
    }

    public function getBlockPrefix(): string
    {
        return '';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'csrf_field_name' => '_csrf_token',
            'csrf_token_id' => 'authenticate',
        ]);
        parent::configureOptions($resolver);
    }
}
