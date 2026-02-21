<?php

namespace App\Foundation\Mailing;

use App\Foundation\Queue\EnqueueMethod;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Crypto\DkimSigner;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Message;
use Twig\Environment;

readonly class MailerBuilder
{
    public function __construct(
        private Environment $twig,
        private EnqueueMethod $enqueue,
        private MailerInterface $mailer,
        private ?string $dkimKey = null,
    ) {
    }

    /**
     * To create an email.
     */
    public function buildEmail(string $template, array $data = []): Email
    {
        // Add the global email formats in the twig loader
        $this->twig->addGlobal('format', 'html');

        $html = $this->twig->render($template, array_merge($data, ['layout' => 'mails/base.html.twig']));
        // To rend a view according to the format (text)
        $this->twig->addGlobal('format', 'text');
        $text = $this->twig->render($template, array_merge($data, ['layout' => 'mails/base.text.twig']));

        // Set up the email
        return (new Email())
            ->from(new Address('noreplay@lakamark.com', 'Email'))
            ->html($html)
            ->text($text);
    }

    /**
     * To send an email.
     *
     * @throws ExceptionInterface
     */
    public function deliveryEmail(Email $email): void
    {
        $this->enqueue->enqueue(self::class, 'sendNow', [$email]);
    }

    /**
     * If you want to send quickly an email to the user.
     * This method will execute in property in the queue transport.
     *
     * @throws TransportExceptionInterface
     */
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
