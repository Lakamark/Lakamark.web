<?php

namespace App\Http\Form\Type\Captcha;

use App\Foundation\Captcha\Puzzle\PuzzleCaptchaChallenge;
use App\Http\Form\Type\CaptchaType;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class PuzzleCaptchaType extends CaptchaType
{
    protected function getCaptchaType(): string
    {
        return 'puzzle';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'width' => PuzzleCaptchaChallenge::WIDTH,
            'height' => PuzzleCaptchaChallenge::HEIGHT,
            'piece_width' => PuzzleCaptchaChallenge::PIECE_WIDTH,
            'piece_height' => PuzzleCaptchaChallenge::PIECE_HEIGHT,
        ]);
    }

    protected function getAdditionalViewVars(array $options): array
    {
        return [
            'captcha_width' => $options['width'],
            'captcha_height' => $options['height'],
            'captcha_piece_width' => $options['piece_width'],
            'captcha_piece_height' => $options['piece_height'],
        ];
    }

    public function getBlockPrefix(): string
    {
        return 'puzzle_captcha';
    }
}
