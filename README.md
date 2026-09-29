# UnifyUnits Laravel SDK

A small Laravel HTTP client for the UnifyUnits Measurement API. It sends values
as decimal strings so PHP floating-point conversion does not alter precision.

## Requirements

- PHP 8.2 or newer
- Laravel 10, 11, 12, or 13

## Install

When published, install through Composer:

```sh
composer require unifyunits/unifyunits-laravel
```

Laravel discovers the service provider automatically. Publish its configuration
and set credentials in the application environment:

```sh
php artisan vendor:publish --tag=unifyunits-config
```

```dotenv
UNIFYUNITS_BASE_URL=https://api.unifyunits.com
UNIFYUNITS_API_KEY=uu_live_your_key
UNIFYUNITS_TIMEOUT=10
```

Keep API keys in server-side environment configuration. Do not expose them in
browser code, source control, or logs.

## Usage

Inject the client contract:

```php
use UnifyUnits\Laravel\Contracts\UnifyUnitsClient;

class QuoteController
{
    public function __invoke(UnifyUnitsClient $units)
    {
        $conversion = $units->convert('1000', 'm', 'km');

        return $conversion['data']['result']; // ['value' => '1', 'unit' => 'km']
    }
}
```

Or use the facade:

```php
use UnifyUnits\Laravel\Facades\UnifyUnits;

$result = UnifyUnits::convert('72', 'mi/h', 'km/h');
```

Available methods are `convert`, `convertBatch`, `categories`, `category`,
`units`, `unit`, and `health`. Conversion values must be strings. Batch requests
return each item's success or error plus aggregate counts; individual item
errors do not make the entire batch response an HTTP failure.

HTTP failures throw `UnifyUnits\Laravel\Exceptions\ApiException`, which offers
`errorCode()`, `requestId()`, and `details()` helpers. Network and timeout
exceptions from Laravel's HTTP client remain distinguishable from API errors.

## Local development

```sh
composer install
composer test
```

Tests use Laravel's HTTP fake and contain no production credentials. Package
release, Packagist publication, and live API verification are separate steps.
