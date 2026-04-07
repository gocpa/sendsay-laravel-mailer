<?php

declare(strict_types=1);

namespace GoCPA\SendsayLaravelMailer\Tests\Feature;

use GoCPA\SendsayLaravelMailer\SendsayMailerTransport;
use GoCPA\SendsayLaravelMailer\Tests\TestCase;
use Illuminate\Support\Facades\Http;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

final class SendsayMailerTransportTest extends TestCase
{
    public function test_registers_sendsay_transport_in_mail_manager(): void
    {
        $transport = $this->app['mail.manager']->createSymfonyTransport([
            'transport' => 'sendsay',
            'account' => 'registered-account',
            'apikey' => 'registered-apikey',
        ]);

        self::assertInstanceOf(SendsayMailerTransport::class, $transport);
    }

    public function test_builds_payload_with_text_and_html_bodies(): void
    {
        Http::fake([
            '*' => Http::response(['ok' => true], 200),
        ]);

        $transport = new SendsayMailerTransport(
            account: 'test-account',
            apikey: 'test-apikey',
        );

        $email = (new Email())
            ->from(new Address('sender@example.com', 'Sender Name'))
            ->to('recipient@example.com')
            ->subject('Payload Subject')
            ->text('Plain text body')
            ->html('<p>Html body</p>');

        $transport->send($email);

        Http::assertSentCount(1);

        /** @var array{0:\Illuminate\Http\Client\Request,1:\Illuminate\Http\Client\Response|null} $record */
        $record = Http::recorded()->first();
        $request = $record[0];
        $data = $request->data();

        self::assertStringContainsString('/general/api/v100/json/test-account', $request->url());
        self::assertSame('POST', $request->method());
        self::assertSame('issue.send', $data['action'] ?? null);
        self::assertSame('recipient@example.com', $data['email'] ?? null);
        self::assertSame('sender@example.com', $data['letter']['from.email'] ?? null);
        self::assertSame('Sender Name', $data['letter']['from.name'] ?? null);
        self::assertSame('Payload Subject', $data['letter']['subject'] ?? null);
        self::assertSame('Plain text body', $data['letter']['message']['text'] ?? null);
        self::assertSame('<p>Html body</p>', $data['letter']['message']['html'] ?? null);
    }

    public function test_throws_transport_exception_when_from_or_to_is_missing(): void
    {
        $transport = new SendsayMailerTransport(
            account: 'test-account',
            apikey: 'test-apikey',
        );

        $withoutFrom = (new Email())
            ->to('recipient@example.com')
            ->subject('No from')
            ->text('body');

        $this->expectException(TransportException::class);
        $this->expectExceptionMessage('From address is required for Sendsay transport.');
        $this->invokePrivate($transport, 'resolveFromAddress', $withoutFrom);
    }

    public function test_throws_transport_exception_when_recipient_is_missing(): void
    {
        $transport = new SendsayMailerTransport(
            account: 'test-account',
            apikey: 'test-apikey',
        );

        $withoutTo = (new Email())
            ->from('sender@example.com')
            ->subject('No to')
            ->text('body');

        $this->expectException(TransportException::class);
        $this->expectExceptionMessage('Recipient address is required for Sendsay transport.');
        $this->invokePrivate($transport, 'resolveToAddress', $withoutTo);
    }

    public function test_adds_proxy_option_when_configured(): void
    {
        Http::shouldReceive('acceptJson')->once()->andReturnSelf();
        Http::shouldReceive('withToken')->once()->with('apikey=test-apikey', 'sendsay')->andReturnSelf();
        Http::shouldReceive('withOptions')->once()->with([
            'proxy' => 'http://127.0.0.1:8080',
        ])->andReturnSelf();

        $transport = new SendsayMailerTransport(
            account: 'test-account',
            apikey: 'test-apikey',
            proxy: 'http://127.0.0.1:8080',
        );

        $response = \Mockery::mock();
        $response->shouldReceive('throw')->once()->andReturnSelf();
        Http::shouldReceive('post')->once()->andReturn($response);

        $email = (new Email())
            ->from(new Address('sender@example.com', 'Sender Name'))
            ->to('recipient@example.com')
            ->subject('Proxy Subject')
            ->text('Proxy body');

        $transport->send($email);
    }

    private function invokePrivate(SendsayMailerTransport $transport, string $methodName, Email $email): mixed
    {
        $method = new \ReflectionMethod(SendsayMailerTransport::class, $methodName);
        $method->setAccessible(true);

        return $method->invoke($transport, $email);
    }
}
