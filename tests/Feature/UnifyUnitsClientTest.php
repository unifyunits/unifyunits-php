<?php

namespace UnifyUnits\Laravel\Tests\Feature;

use Illuminate\Support\Facades\Http;
use Orchestra\Testbench\TestCase;
use UnifyUnits\Laravel\Exceptions\ApiException;
use UnifyUnits\Laravel\UnifyUnitsClient;

class UnifyUnitsClientTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [\UnifyUnits\Laravel\UnifyUnitsServiceProvider::class];
    }

    public function test_conversion_uses_decimal_strings_and_bearer_auth(): void
    {
        Http::fake(['api.test/v1/convert' => Http::response(['data' => ['result' => ['value' => '1', 'unit' => 'km']]])]);

        $client = new UnifyUnitsClient('https://api.test', 'uu_live_test');
        self::assertSame('1', $client->convert('1000', 'm', 'km')['data']['result']['value']);
        Http::assertSent(fn ($request) => $request->hasHeader('Authorization', 'Bearer uu_live_test')
            && $request['value'] === '1000'
            && $request->url() === 'https://api.test/v1/convert');
    }

    public function test_batch_preserves_per_item_results(): void
    {
        Http::fake(['api.test/v1/batch' => Http::response(['data' => [], 'meta' => ['total' => 1, 'succeeded' => 1, 'failed' => 0]])]);
        $client = new UnifyUnitsClient('https://api.test', 'test-key');
        $result = $client->convertBatch([['value' => '1.25', 'from' => 'm', 'to' => 'cm']]);
        self::assertSame(1, $result['meta']['succeeded']);
        Http::assertSent(fn ($request) => $request->data()['conversions'][0]['value'] === '1.25');
    }

    public function test_api_errors_keep_machine_code_and_request_id(): void
    {
        Http::fake(['api.test/v1/convert' => Http::response(['error' => ['code' => 'UNKNOWN_UNIT', 'message' => 'Unknown unit', 'request_id' => 'req-1']], 400)]);
        try {
            (new UnifyUnitsClient('https://api.test', 'test-key'))->convert('1', 'bad', 'm');
            self::fail('Expected an API exception.');
        } catch (ApiException $exception) {
            self::assertSame('UNKNOWN_UNIT', $exception->errorCode());
            self::assertSame('req-1', $exception->requestId());
        }
    }
}
