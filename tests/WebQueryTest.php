<?php

declare(strict_types=1);

namespace Ray\MediaQuery;

use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;
use Ray\MediaQuery\Exception\WebApiRequestException;

use function dirname;
use function file_get_contents;

class WebQueryTest extends TestCase
{
    private function webQuery(): WebApiQuery
    {
        $schema = (string) file_get_contents(dirname(__DIR__) . '/docs/web_query.json');

        return new WebApiQuery(new FakeWebClient($schema), new MediaQueryLogger(), ['domain1' => 'ray-di.github.io']);
    }

    public function testRequest(): void
    {
        $uri = 'https://{domain1}/Ray.MediaQuery/schema/{id}.json';
        $response = $this->webQuery()->request('GET', $uri, ['id' => 'web_query']);
        $this->assertSame('Web query schema', $response['title']);
    }

    public function testGetStringBody(): void
    {
        $uri = 'https://{domain1}/Ray.MediaQuery/schema/{id}.json';
        $response = $this->webQuery()->getStringBody('GET', $uri, ['id' => 'web_query']);
        $this->assertStringContainsString('"title": "Web query schema"', $response);
    }

    public function testGetHttpMessage(): void
    {
        $uri = 'https://{domain1}/Ray.MediaQuery/schema/{id}.json';
        $response = $this->webQuery()->getHttpMessage('GET', $uri, ['id' => 'web_query']);
        $this->assertStringContainsString('"title": "Web query schema"', $response->getBody()->getContents());
    }

    public function testInvalidRequest(): void
    {
        $this->expectException(WebApiRequestException::class);
        $webQuery = new WebApiQuery(new Client(), new MediaQueryLogger(), []);
        $webQuery->request('GET', 'https://__invalid__/', []);
    }

    public function testInvalidRequestStringBody(): void
    {
        $this->expectException(WebApiRequestException::class);
        $webQuery = new WebApiQuery(new Client(), new MediaQueryLogger(), []);
        $webQuery->getStringBody('GET', 'https://__invalid__/', []);
    }

    public function testInvalidRequestHttpMessage(): void
    {
        $this->expectException(WebApiRequestException::class);
        $webQuery = new WebApiQuery(new Client(), new MediaQueryLogger(), []);
        $webQuery->getHttpMessage('GET', 'https://__invalid__/', []);
    }
}
