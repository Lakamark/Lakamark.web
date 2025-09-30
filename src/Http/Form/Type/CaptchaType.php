<?php

namespace App\Http\Form\Type;

use App\Domain\Captcha\CaptchaChallengeInterface;
use App\Domain\Captcha\Puzzle\PuzzleChallenge;
use App\Validator\Captcha;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class CaptchaType extends AbstractType
{
    public function __construct(
        private readonly CaptchaChallengeInterface $captchaChallenge,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'constraints' => [
                new NotBlank(),
                new Captcha(),
            ],
            'route' => 'captcha',
        ]);
        parent::configureOptions($resolver);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('challenge', HiddenType::class, [
                'attr' => [
                    'class' => 'captcha-challenge',
                ],
            ])
            ->add('answer', HiddenType::class, [
                'attr' => [
                    'class' => 'captcha-answer',
                ],
            ]);
        parent::buildForm($builder, $options);
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $key = $this->captchaChallenge->generateKey();
        $view->vars['attr'] = [
            'width' => PuzzleChallenge::WIDTH,
            'height' => PuzzleChallenge::HEIGHT,
            'piece_width' => PuzzleChallenge::PIECE_WIDTH,
            'piece_height' => PuzzleChallenge::PIECE_HEIGHT,
            'src' => $this->urlGenerator->generate($options['route'], ['challenge' => $key]),
        ];
        $view->vars['challenge'] = $key;
        parent::buildView($view, $form, $options);
    }
}
