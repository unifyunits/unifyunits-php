<?php

namespace UnifyUnits\Laravel\Exceptions;

use Illuminate\Http\Client\RequestException;

class ApiException extends RequestException
{
    public function errorCode(): ?string
    {
        return $this->response->json('error.code');
    }

    public function requestId(): ?string
    {
        return $this->response->json('error.request_id')
            ?? $this->response->header('X-Request-Id');
    }

    public function details(): ?array
    {
        return $this->response->json('error.details');
    }
}
