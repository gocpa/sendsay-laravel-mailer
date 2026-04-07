<?php

declare(strict_types=1);

namespace GoCPA\SendsayLaravelMailer;

use Illuminate\Support\Facades\Http;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\MessageConverter;
use Throwable;

class SendsayMailerTransport extends AbstractTransport
{
    public function __construct(
        protected string $account,
        protected string $apikey,
        protected ?string $proxy,
        protected ?string $dkimId,
    ) {
        parent::__construct();
    }

    /**
     * @throws TransportExceptionInterface
     */
    protected function doSend(SentMessage $message): void
    {
        try {
            $email = MessageConverter::toEmail($message->getOriginalMessage());

            Http::acceptJson()
                ->withOptions($this->getOptions())
                ->withToken('apikey='.$this->apikey, 'sendsay')
                ->post($this->getEndpoint(), $this->getPayload($email))
                ->throw();
        } catch (Throwable $exception) {
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

    private function getPayload(Email $email): array
    {
        $from = $this->firstAddress($email->getFrom(), 'from');
        $to = $this->firstAddress($email->getTo(), 'to');

        $payload = [
            'letter' => [
                'from.email' => $from->getAddress(),
                'from.name' => $from->getName(),
                'subject' => $email->getSubject(),
                'message' => [],
            ],
            'sendwhen' => 'now',
            'email' => $to->getAddress(),
            'group' => 'personal',
            'action' => 'issue.send',
            'apikey' => $this->apikey,
            'dkim.id' => $this->dkimId,
        ];

        if ($email->getTextBody()) {
            $payload['letter']['message']['text'] = $email->getTextBody();
        }
        if ($email->getHtmlBody()) {
            $payload['letter']['message']['html'] = $email->getHtmlBody();
        }

        return $payload;
    }

    /**
     * @param array<int, Address> $addresses
     */
    private function firstAddress(array $addresses, string $field): Address
    {
        $address = $addresses[0] ?? null;

        if (! $address instanceof Address) {
            throw new TransportException(sprintf('Email "%s" address is required for Sendsay transport.', $field));
        }

        return $address;
    }

    public function getEndpoint(): string
    {
        return 'https://api.sendsay.ru/general/api/v100/json/'.$this->account;
    }

    public function getOptions(): array
    {
        $options = [];

        if ($this->proxy) {
            $options['proxy'] = $this->proxy;
        }

        return $options;
    }
}
