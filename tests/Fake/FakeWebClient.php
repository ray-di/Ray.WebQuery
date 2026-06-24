<?php

declare(strict_types=1);

namespace Ray\MediaQuery;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Fake Guzzle client that serves a canned response body.
 *
 * Replaces live HTTP access in tests so the suite stays hermetic and does not
 * depend on an external URL being reachable.
 */
final class FakeWebClient implements ClientInterface
{
    public function __construct(
        private string $body,
        private int $status = 200,
    ) {
    }

    public function request(string $method, $uri = '', array $options = []): ResponseInterface
    {
        return new Response($this->status, ['Content-Type' => 'application/json'], $this->body);
    }

    public function send(RequestInterface $request, array $options = []): ResponseInterface
    {
        return new Response($this->status, ['Content-Type' => 'application/json'], $this->body);
    }

    public function sendAsync(RequestInterface $request, array $options = []): PromiseInterface
    {
        return new FulfilledPromise($this->send($request, $options));
    }

    public function requestAsync(string $method, $uri = '', array $options = []): PromiseInterface
    {
        return new FulfilledPromise($this->request($method, $uri, $options));
    }

    /** @return mixed */
    public function getConfig(?string $option = null)
    {
        return null;
    }
}
