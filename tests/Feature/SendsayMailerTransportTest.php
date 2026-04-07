<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use GoCPA\SendsayLaravelMailer\SendsayMailerTransport;

it('registers sendsay transport in mail manager', function (): void {
    $transport = app('mail.manager')->createSymfonyTransport([
        'transport' => 'sendsay',
        'account' => 'registered-account',
        'apikey' => 'registered-apikey',
    ]);

    expect($transport)->toBeInstanceOf(SendsayMailerTransport::class);
});

it('builds payload with text and html bodies', function (): void {
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

    Http::assertSent(function (\Illuminate\Http\Client\Request $request): bool {
        $data = $request->data();

        return $request->url() === 'https://api.sendsay.ru/general/api/v100/json/test-account'
            && $request->method() === 'POST'
            && $request->hasHeader('Authorization', 'sendsay apikey=test-apikey')
            && ($data['action'] ?? null) === 'issue.send'
            && ($data['email'] ?? null) === 'recipient@example.com'
            && ($data['group'] ?? null) === 'personal'
            && ($data['sendwhen'] ?? null) === 'now'
            && ($data['letter']['from']['email'] ?? null) === 'sender@example.com'
            && ($data['letter']['from']['name'] ?? null) === 'Sender Name'
            && ($data['letter']['subject'] ?? null) === 'Payload Subject'
            && ($data['letter']['message']['text'] ?? null) === 'Plain text body'
            && ($data['letter']['message']['html'] ?? null) === '<p>Html body</p>';
    });
});

it('throws transport exception when from or to is missing', function (): void {
    $transport = new SendsayMailerTransport(
        account: 'test-account',
        apikey: 'test-apikey',
    );

    $withoutFrom = (new Email())
        ->to('recipient@example.com')
        ->subject('No from')
        ->text('body');

    expect(fn (): mixed => invokePrivate($transport, 'resolveFromAddress', $withoutFrom))
        ->toThrow(TransportException::class, 'From address is required for Sendsay transport.');

    $withoutTo = (new Email())
        ->from('sender@example.com')
        ->subject('No to')
        ->text('body');

    expect(fn (): mixed => invokePrivate($transport, 'resolveToAddress', $withoutTo))
        ->toThrow(TransportException::class, 'Recipient address is required for Sendsay transport.');
});

it('adds proxy option when configured', function (): void {
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
});

function invokePrivate(SendsayMailerTransport $transport, string $methodName, Email $email): mixed
{
    $method = new ReflectionMethod(SendsayMailerTransport::class, $methodName);
    $method->setAccessible(true);

    return $method->invoke($transport, $email);
}
