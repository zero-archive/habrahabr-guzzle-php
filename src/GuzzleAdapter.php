<?php

namespace Habrahabr\Api\HttpAdapter;

use Habrahabr\Api\Exception\NetworkException;
use GuzzleHttp\Psr7;
use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;

/**
 * Class GuzzleAdapter
 *
 * Habrahabr Api HTTP адаптер использующий Guzzle, PHP HTTP client как транспорт
 *
 * @package Habrahabr\Api\HttpAdapter
 * @version 0.1.0
 * @author dotzero <mail@dotzero.ru>
 * @link http://www.dotzero.ru/
 * @link https://habrahabr.ru/
 * @link https://github.com/dotzero/habrahabr-guzzle-php
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class GuzzleAdapter extends BaseAdapter implements HttpAdapterInterface
{
    /**
     * @var bool Строгая проверка SSL сертификата
     */
    protected $strictSSL = true;

    /**
     * @var array Дополнительные параметры передаваемые в \GuzzleHttp\Client
     */
    protected $guzzleConfig = [];

    /**
     * GuzzleAdapter constructor
     *
     * @param array $config Дополнительные параметры передаваемые в \GuzzleHttp\Client
     */
    public function __construct(array $config = [])
    {
        if ($config) {
            $this->guzzleConfig = $config;
        }
    }

    /**
     * Выполнить HTTP GET запрос и вернуть тело ответа
     *
     * @param string $url URL суффикс запрашиваемого ресурса
     * @return array
     * @throws NetworkException
     */
    public function get($url)
    {
        $url = $this->createUrl($url);

        $response = $this->getGuzzleClient()->get($url);

        return $this->parseResponse($response);
    }

    /**
     * Выполнить HTTP POST запрос и вернуть тело ответа
     *
     * @param string $url URL суффикс запрашиваемого ресурса
     * @param array $params Параметры, передаваемые в теле запроса
     * @return array
     * @throws NetworkException
     */
    public function post($url, array $params = [])
    {
        $url = $this->createUrl($url);

        $response = $this->getGuzzleClient()->post($url, [
            'form_params' => $params
        ]);

        return $this->parseResponse($response);
    }

    /**
     * Выполнить HTTP PUT запрос и вернуть тело ответа
     *
     * @param string $url URL суффикс запрашиваемого ресурса
     * @param array $params Параметры, передаваемые в теле запроса
     * @return array
     * @throws NetworkException
     */
    public function put($url, array $params = [])
    {
        $url = $this->createUrl($url);

        $response = $this->getGuzzleClient()->put($url, [
            'form_params' => $params
        ]);

        return $this->parseResponse($response);
    }

    /**
     * Выполнить HTTP DELETE запрос и вернуть тело ответа
     *
     * @param string $url URL суффикс запрашиваемого ресурса
     * @param array $params Параметры, передаваемые в теле запроса
     * @return array
     * @throws NetworkException
     */
    public function delete($url)
    {
        $url = $this->createUrl($url);

        $response = $this->getGuzzleClient()->delete($url);

        return $this->parseResponse($response);
    }

    /**
     * Устанавливает или убирает строгую проверку SSL сертификата
     *
     * @param bool $flag Флаг строгой проверки SSL сертификата
     * @return $this
     */
    public function setStrictSSL($flag = true)
    {
        $this->strictSSL = $flag;

        return $this;
    }

    /**
     * Возвращает параметры передаваемые в \GuzzleHttp\Client
     *
     * @return array
     */
    public function getGuzzleConfig()
    {
        $config = array_merge([
            'headers' => [],
            'http_errors' => false,
            'verify' => $this->strictSSL,
            'timeout' => $this->getConnectionTimeout(),
        ], $this->guzzleConfig);

        if ($this->client !== null && $this->token !== null) {
            $config['headers']['client'] = $this->client;
            $config['headers']['token'] = $this->token;
        } elseif ($this->apikey !== null) {
            $config['headers']['apikey'] = $this->apikey;
        }

        return $config;
    }

    /**
     * @return \GuzzleHttp\Client
     */
    private function getGuzzleClient()
    {
        return new Client($this->getGuzzleConfig());
    }

    /**
     * @param ResponseInterface $response
     * @return array
     * @throws NetworkException
     */
    private function parseResponse(ResponseInterface $response)
    {
        $header = Psr7\parse_header($response->getHeaderLine('content-type'));

        if (!isset($header[0][0]) || $header[0][0] !== 'application/json') {
            throw new NetworkException(
                $response->getReasonPhrase(),
                $response->getStatusCode()
            );
        }

        $result = json_decode($response->getBody(), true);

        return $result ? $result : [];
    }
}
