<?php

declare(strict_types=1);

namespace GoCPA\SendsayLaravelMailer;

use Illuminate\Support\Facades\Http;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\MessageConverter;

final class SendsayMailerTransport extends AbstractTransport
{
    public function __construct(
        private readonly string $account,
        private readonly string $apikey,
        private readonly ?string $proxy = null,
        private readonly ?string $dkimId = null
    ) {
        parent::__construct();
    }

    protected function doSend(SentMessage $message): void
    {
        try {
            $email = MessageConverter::toEmail($message->getOriginalMessage());

            $from = $this->resolveFromAddress($email);
            $to = $this->resolveToAddress($email);

            $payload = [
                'action' => 'issue.send',
                'apikey' => $this->apikey,
                'email' => $to->getAddress(),
                'group' => 'personal',
                'sendwhen' => 'now',
                'letter' => [
                    'from.email' => $from->getAddress(),
                    'from.name' => $from->getName(),
                    'subject' => $email->getSubject() ?? '',
                    'message' => array_filter([
                        'text' => $email->getTextBody(),
                        'html' => $email->getHtmlBody(),
                    ], static fn (mixed $value): bool => is_string($value) && $value !== ''),
                ],
                'dkim.id' => $this->dkimId,
            ];

            $request = Http::acceptJson()
                ->withToken('apikey=' . $this->apikey, 'sendsay');

            if ($this->proxy !== null && $this->proxy !== '') {
                $request = $request->withOptions([
                    'proxy' => $this->proxy,
                ]);
            }

            $request
                ->post($this->endpoint(), $payload)
                ->throw();
        } catch (\Throwable $exception) {
            throw new TransportException(
                'Sendsay delivery failed: '.$exception->getMessage(),
                0,
                $exception,
            );
        }
    }

    public function __toString(): string
    {
        return 'sendsay';
    }

    private function endpoint(): string
    {
        return sprintf(
            'https://api.sendsay.ru/general/api/v100/json/%s',
            $this->account
        );
    }

    private function resolveFromAddress(Email $email): Address
    {
        $from = $email->getFrom()[0] ?? null;

        if (! $from instanceof Address) {
            throw new TransportException('From address is required for Sendsay transport.');
        }

        return $from;
    }

    private function resolveToAddress(Email $email): Address
    {
        $to = $email->getTo()[0] ?? null;

        if (! $to instanceof Address) {
            throw new TransportException('Recipient address is required for Sendsay transport.');
        }

        return $to;
    }
}
