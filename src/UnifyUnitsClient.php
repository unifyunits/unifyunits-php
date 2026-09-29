<?php

namespace UnifyUnits\Laravel;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use UnifyUnits\Laravel\Contracts\UnifyUnitsClient as ClientContract;
use UnifyUnits\Laravel\Exceptions\ApiException;

class UnifyUnitsClient implements ClientContract
{
    private PendingRequest $http;

    public function __construct(string $baseUrl, ?string $apiKey, float $timeout = 10)
    {
        $this->http = Http::baseUrl(rtrim($baseUrl, '/'))
            ->acceptJson()
            ->timeout($timeout);

        if ($apiKey !== null && $apiKey !== '') {
            $this->http->withToken($apiKey);
        }
    }

    public function convert(string $value, string $from, string $to): array
    {
        return $this->send(fn () => $this->http->post('/v1/convert', [
            'value' => $value,
            'from' => $from,
            'to' => $to,
        ]));
    }

    public function convertBatch(array $conversions): array
    {
        if ($conversions === []) {
            throw new InvalidArgumentException('At least one conversion is required.');
        }

        foreach ($conversions as $conversion) {
            if (! is_array($conversion) || ! isset($conversion['value'], $conversion['from'], $conversion['to'])) {
                throw new InvalidArgumentException('Each conversion needs string value, from, and to fields.');
            }
            foreach (['value', 'from', 'to'] as $field) {
                if (! is_string($conversion[$field])) {
                    throw new InvalidArgumentException("Conversion field {$field} must be a string.");
                }
            }
        }

        return $this->send(fn () => $this->http->post('/v1/batch', ['conversions' => array_values($conversions)]));
    }

    public function categories(): array
    {
        return $this->send(fn () => $this->http->get('/v1/categories'));
    }

    public function category(string $category): array
    {
        return $this->send(fn () => $this->http->get('/v1/categories/'.rawurlencode($category)));
    }

    public function units(?string $category = null): array
    {
        return $this->send(fn () => $this->http->get('/v1/units', array_filter(['category' => $category], fn ($value) => $value !== null)));
    }

    public function unit(string $unit): array
    {
        return $this->send(fn () => $this->http->get('/v1/units/'.rawurlencode($unit)));
    }

    public function health(): array
    {
        return $this->send(fn () => $this->http->get('/v1/health'));
    }

    private function send(callable $request): array
    {
        $response = $request();

        try {
            $response->throw();
        } catch (RequestException $exception) {
            throw new ApiException($exception->response, $exception->getMessage(), $exception->getCode(), $exception);
        }

        return $response->json();
    }
}
