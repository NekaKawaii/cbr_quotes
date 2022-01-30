<?php

declare(strict_types=1);

namespace App\Tests\_tools\Fake;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Promise\Promise;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Fake GuzzleHTTP Client for test purposes
 */
final class FakeGuzzleClient implements ClientInterface
{
    /**
     * @var array<string, array<string, Response>>
     */
    private array $uri = [];

    /**
     * Put content on URI for further request to this URI
     */
    public function putContentOnURI(string $method, string $uri, string $content): void
    {
        $this->uri[$method][$uri] = new Response(status: 200, body: $content);
    }

    /**
     * @inheritDoc
     */
    public function request(string $method, $uri = '', array $options = []): ResponseInterface
    {
        return $this->uri[$method][$uri] ?? new Response(status: 500);
    }

    /**
     * @inheritDoc
     */
    public function send(RequestInterface $request, array $options = []): ResponseInterface
    {
        return new Response();
    }

    /**
     * @inheritDoc
     */
    public function sendAsync(RequestInterface $request, array $options = []): PromiseInterface
    {
        return new Promise();
    }

    /**
     * @inheritDoc
     */
    public function requestAsync(string $method, $uri, array $options = []): PromiseInterface
    {
        return new Promise();
    }

    /**
     * @inheritDoc
     */
    public function getConfig(?string $option = null)
    {
        return null;
    }
}
