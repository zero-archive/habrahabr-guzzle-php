# Habrahabr Api HTTP адаптер использующий Http Guzzle

[![Latest Stable Version](https://poser.pugx.org/dotzero/habrahabr_guzzle/version)](https://packagist.org/packages/dotzero/habrahabr_guzzle)
[![License](https://poser.pugx.org/dotzero/habrahabr_guzzle/license)](https://packagist.org/packages/dotzero/habrahabr_guzzle)

## Установка

### Через composer:

```bash
$ composer require dotzero/habrahabr_guzzle
```

или добавить

```json
"dotzero/habrahabr_guzzle": "0.1.*"
```

в секцию `require` файла composer.json.

## Быстрый старт

```php
$adapter = new \Habrahabr\Api\HttpAdapter\GuzzleAdapter();

$adapter->setEndpoint(getenv('ENDPOINT'));
$adapter->setToken(getenv('TOKEN'));
$adapter->setClient(getenv('CLIENT'));

$client = new \Habrahabr\Api\Client($adapter);

$User = $client->getUserResource()->getUser('me');
var_dump($User);
```

## Тестирование

Для начала установить `--dev` зависимости. После чего запустить:

```bash
$ vendor/bin/phpunit
```

## Лицензия

Библиотека доступна на условиях лицензии MIT: http://www.opensource.org/licenses/mit-license.php
