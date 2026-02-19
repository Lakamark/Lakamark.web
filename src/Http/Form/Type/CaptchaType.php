<?php

namespace App\Http\Form\Type;

use App\Foundation\Captcha\CaptchaService;
use App\Validator\CaptchaValid;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

abstract class CaptchaType extends AbstractType
{
    public function __construct(
        private readonly CaptchaService $captchaService,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    /**
     * Ex: puzzle.
     */
    abstract protected function getCaptchaType(): string;

    /**
     * Ex: app_captcha.
     */
    protected function getCaptchaRoute(): string
    {
        return 'app_captcha';
    }

    /**
     * Children class can add view vars.
     */
    protected function getAdditionalViewVars(array $options): array
    {
        return [];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'mapped' => false,
        ]);
        parent::configureOptions($resolver);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'challenge', HiddenType::class, [
                    'data' => $this->captchaService->getKey(),
                    'attr' => [
                        'class' => 'captcha-challenge',
                    ],
                ])
            ->add('answer', HiddenType::class, [
                'attr' => [
                    'class' => 'captcha-answer',
                ],
                'constraints' => [
                    new NotBlank(),
                    new CaptchaValid(),
                ],
            ]);
        parent::buildForm($builder, $options);
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $type = $this->getCaptchaType();
        $uri = $this->urlGenerator->generate($this->getCaptchaRoute(), [
            'type' => $type,
        ]);

        // Set the default view vars for all captcha.
        $view->vars['captcha_type'] = $type;
        $view->vars['captcha_src'] = $uri;

        // Add custom children class view vars
        foreach ($this->getAdditionalViewVars($options) as $key => $value) {
            $view->vars[$key] = $value;
        }
        parent::buildView($view, $form, $options);
    }
}
