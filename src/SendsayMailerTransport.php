<?php

namespace GoCPA\SendsayLaravelMailer;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Mailer\Exception\HttpTransportException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\MessageConverter;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;

class SendsayMailerTransport extends AbstractTransport
{
    protected $client;

    public function __construct(
        protected string $account,
        protected string $apikey,
    ) {
        $this->client = HttpClient::create();

        parent::__construct();
    }

    /**
     * @throws TransportExceptionInterface
     */
    protected function doSend(SentMessage $message): void
    {
        $email = MessageConverter::toEmail($message->getOriginalMessage());

        $headers = [
            'Authorization: sendsay apikey=' . $this->apikey,
        ];

        $payload = $this->getPayload($email);

        $response = $this->client->request('POST', $this->getEndpoint(), [
            'headers' => $headers,
            'json' => $payload,
        ]);

        $result = $response->getContent(true);

        try {
            $statusCode = $response->getStatusCode();
            if (200 !== $statusCode) {
                throw new HttpTransportException('Unable to send an email: ' . $result['message'] . sprintf(' (code %d).', $statusCode), $response);
            }
            $result = $response->toArray(false);
            logger()->debug('Отправлено сообщение в sendsay', [
                'payload' => $payload,
                'response' => $response,
            ]);
        } catch (DecodingExceptionInterface) {
            throw new HttpTransportException('Unable to send an email: ' . $response->getContent(false) . sprintf(' (code %d).', $statusCode), $response);
        } catch (TransportExceptionInterface $e) {
            throw new HttpTransportException('Could not reach the remote sendsay server.', $response, 0, $e);
        }

        // // TODO: узнать номер сообщения
        // // $sentMessage->setMessageId($result['id']);
    }

    public function __toString(): string
    {
        return 'sendsay';
    }

    private function getPayload(Email $email): array
    {
        $html = $email->getHtmlBody();

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
        if ($html) {
            $payload['letter']['message']['html'] = $html;
        }

        return $payload;
    }

    public function getEndpoint(): string
    {
        return 'https://api.sendsay.ru/general/api/v100/json/' . $this->account;
    }
}
