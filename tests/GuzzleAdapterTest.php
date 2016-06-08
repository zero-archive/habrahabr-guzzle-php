<?php

namespace Habrahabr\Tests\Api;

use Habrahabr\Api\HttpAdapter\GuzzleAdapter;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;


class GuzzleAdapterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MockHandler
     */
    protected $mock;

    /**
     * @var GuzzleAdapter
     */
    protected $adapter;

    protected function setUp()
    {
        $this->mock = new MockHandler([
            new Response(200,
                ['Content-Type' => 'application/json'],
                json_encode(['data' => []])
            ),
        ]);

        $this->adapter = new GuzzleAdapter(['handler' => $this->mock]);
        $this->adapter->setEndpoint('https://habrahabr.ru/');
    }

    public function testGet()
    {
        $res = $this->adapter->get('/foobar?page=1');

        $this->assertArrayHasKey('data', $res);
        $this->assertInternalType('array', $res['data']);

        $req = $this->mock->getLastRequest();

        $this->assertEquals('GET', $req->getMethod());
        $this->assertEquals($this->adapter->getEndpoint() . '/foobar?page=1', (string)$req->getUri());
    }

    public function testPost()
    {
        $res = $this->adapter->post('/foobar', [
            'foo' => 'bar bam',
            'baz' => ['boo' => 'qux']
        ]);

        $this->assertArrayHasKey('data', $res);
        $this->assertInternalType('array', $res['data']);

        $req = $this->mock->getLastRequest();

        $this->assertEquals('POST', $req->getMethod());
        $this->assertEquals($this->adapter->getEndpoint() . '/foobar', (string)$req->getUri());

        $this->assertEquals(
            'application/x-www-form-urlencoded',
            $req->getHeaderLine('Content-Type')
        );

        $this->assertEquals(
            'foo=bar+bam&baz%5Bboo%5D=qux',
            (string)$req->getBody()
        );
    }

    public function testPut()
    {
        $res = $this->adapter->put('/foobar', [
            'foo' => 'bar bam',
            'baz' => ['boo' => 'qux']
        ]);

        $this->assertArrayHasKey('data', $res);
        $this->assertInternalType('array', $res['data']);

        $req = $this->mock->getLastRequest();

        $this->assertEquals('PUT', $req->getMethod());
        $this->assertEquals($this->adapter->getEndpoint() . '/foobar', (string)$req->getUri());

        $this->assertEquals(
            'application/x-www-form-urlencoded',
            $req->getHeaderLine('Content-Type')
        );

        $this->assertEquals(
            'foo=bar+bam&baz%5Bboo%5D=qux',
            (string)$req->getBody()
        );
    }

    public function testDelete()
    {
        $res = $this->adapter->delete('/foobar?page=1');

        $this->assertArrayHasKey('data', $res);
        $this->assertInternalType('array', $res['data']);

        $req = $this->mock->getLastRequest();

        $this->assertEquals('DELETE', $req->getMethod());
        $this->assertEquals($this->adapter->getEndpoint() . '/foobar?page=1', (string)$req->getUri());
    }

    public function testSetStrictSSL()
    {
        $this->assertAttributeEquals(true, 'strictSSL', $this->adapter);
        $this->adapter->setStrictSSL(false);
        $this->assertAttributeEquals(false, 'strictSSL', $this->adapter);
    }

    public function testGetGuzzleConfig()
    {
        $this->adapter = new GuzzleAdapter([
            'handler' => $this->mock,
            'http_errors' => true,
            'headers' => [
                'X-Foo' => 'Bar',
            ]
        ]);
        $this->adapter->setStrictSSL(false);
        $this->adapter->setConnectionTimeout(100);
        $this->adapter->setEndpoint('https://foobar.com');
        $this->adapter->setApikey('foobar');

        $config = $this->adapter->getGuzzleConfig();

        $this->assertInstanceOf('GuzzleHttp\Handler\MockHandler', $config['handler']);
        $this->assertTrue($config['http_errors']);
        $this->assertFalse($config['verify']);
        $this->assertEquals(100, $config['timeout']);
        $this->assertEquals('Bar', $config['headers']['X-Foo']);
        $this->assertEquals('foobar', $config['headers']['apikey']);

        $this->adapter->setClient('foo.bar');
        $this->adapter->setToken('foobar');

        $config = $this->adapter->getGuzzleConfig();

        $this->assertEquals('foo.bar', $config['headers']['client']);
        $this->assertEquals('foobar', $config['headers']['token']);
    }

    /**
     * @expectedException \Habrahabr\Api\Exception\NetworkException
     */
    public function testParseResponse()
    {
        $this->mock = new MockHandler([
            new Response(),
        ]);

        $this->adapter = new GuzzleAdapter(['handler' => $this->mock]);

        $this->adapter->get('/foobar');
    }
}
