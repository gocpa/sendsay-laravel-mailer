<?php

namespace GoCPA\SendsayLaravelMailer;

use Illuminate\Support\Facades\Http;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\MessageConverter;

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
        $email = MessageConverter::toEmail($message->getOriginalMessage());
        $payload = $this->getPayload($email);
        $options = $this->getOptions();

        $response = Http::acceptJson()
            ->withOptions($options)
            ->withToken('apikey='.$this->apikey, 'sendsay')
            ->post($this->getEndpoint(), $payload)
            ->throw()
            ->json();
    }

    public function __toString(): string
    {
        return 'sendsay';
    }

    private function getPayload(Email $email): array
    {
        $from = collect($email->getFrom())->first();
        $to = collect($email->getTo())->first();

        $payload = [
            'letter' => [
                'from' => [
                    'email' => $from->getAddress(),
                    'name' => $from->getName(),
                ],
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
