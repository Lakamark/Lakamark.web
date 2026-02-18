<?php

namespace App\Http\Form\Type;

use App\Validator\CaptchaValid;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

final class CaptchaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('answer', HiddenType::class, [
                'mapped' => false,
                'constraints' => [
                    new NotBlank(),
                    new CaptchaValid(type: $options['captcha_type']),
                ],
            ]);
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['captcha_url'] = $options['captcha_url'] ?? null;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'captcha_type' => 'puzzle',
        ]);

        $resolver->addAllowedTypes('captcha_type', ['string']);
    }
}
