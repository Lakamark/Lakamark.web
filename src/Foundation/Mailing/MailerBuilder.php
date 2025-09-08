<?php

namespace App\Foundation\Mailing;

use App\Foundation\Queue\EnqueueMethod;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Crypto\DkimSigner;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Message;
use Twig\Environment;

class MailerBuilder
{
    public function __construct(
        private readonly Environment $twig,
        private readonly EnqueueMethod $enqueue,
        private readonly MailerInterface $mailer,
        private readonly ?string $dkimKey = null,
    ) {
    }

    public function buildEmail(string $template, array $data = []): Email
    {
        // Add the global email formats in the twig loader
        $this->twig->addGlobal('format', 'html');
        $this->twig->addGlobal('format', 'text');

        // To rend a view according to the format (html or text)
        $html = $this->twig->render($template, array_merge($data, ['layout' => 'mailers/base.html.twig']));
        $text = $this->twig->render($template, array_merge($data, ['layout' => 'mailers/base.text.twig']));

        // Set up the email
        return (new Email())
            ->from(new Address('noreplay@lakamark.com', 'Email'))
            ->html($html)
            ->text($text);
    }

    public function sendEmail(Email $email): void
    {
        $this->enqueue->enqueue(self::class, 'sendNow', [$email]);
    }

    public function sendNow(Email $email): void
    {
        if ($this->dkimKey) {
            $dkimSigner = new DkimSigner("file://{$this->dkimKey}", 'lakamark.com', 'default');
            // We should manually sign the email temporary https://github.com/symfony/symfony/issues/40131
            $message = new Message($email->getPreparedHeaders(), $email->getBody());
            $email = $dkimSigner->sign($message);
        }
        $this->mailer->send($email);
    }
}
