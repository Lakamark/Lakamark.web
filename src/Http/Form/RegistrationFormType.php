<?php

namespace App\Http\Form;

use App\Domain\Auth\Entity\User;
use App\Http\Form\Type\Captcha\PuzzleCaptchaType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email')
            ->add('username')
            ->add('plainPassword', PasswordType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank(),
                    new Length(
                        min: 6,
                        max: 4096,
                        minMessage: 'Your password should be at least {{ limit }} characters'
                    ),
                ],
            ]);

        // Activate  Captcha
        if ($options['with_captcha_puzzle']) {
            $builder->add('captcha', PuzzleCaptchaType::class, [
                'mapped' => false,
            ]);
        }

        $builder->add('submit', SubmitType::class, [
            'label' => 'Register',
            'attr' => [
                'class' => 'btn btn-lg btn-full btn-primary',
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'with_captcha_puzzle' => true,
        ]);
    }
}
