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
            ->post(
                $this->getEndpoint(),
                $payload
            )
            ->throw()
            ->json();

        logger()->debug('Отправлено сообщение в sendsay', [
            'payload' => $payload,
            'response' => $response,
        ]);
    }

    public function __toString(): string
    {
        return 'sendsay';
    }

    private function getPayload(Email $email): array
    {
        $payload = [
            'letter' => [
                'from.email' => collect($email->getFrom())->first()->getAddress(),
                'from.name' => collect($email->getFrom())->first()->getName(),
                'subject' => $email->getSubject(),
                'message' => [],
            ],
            'sendwhen' => 'now',
            'users.list' => collect($email->getTo())->first()->getAddress(),
            'group' => 'masssending',
            'action' => 'issue.send',
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
        $result = [];
        if ($this->proxy) {
            $result['proxy'] = $this->proxy;
        }

        return $result;
    }
}
