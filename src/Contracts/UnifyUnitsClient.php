<?php

namespace UnifyUnits\Laravel\Contracts;

interface UnifyUnitsClient
{
    public function convert(string $value, string $from, string $to): array;

    public function convertBatch(array $conversions): array;

    public function categories(): array;

    public function category(string $category): array;

    public function units(?string $category = null): array;

    public function unit(string $unit): array;

    public function health(): array;
}
