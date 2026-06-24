<?php

namespace app\interfaces;

interface ProviderInterface
{
    public function getServices(): array;

    public function order(string $serviceCode, string $target, ?string $zone = null, ?string $externalId = null): array;

    public function checkStatus(string $trxId): array;

    public function getNickname(string $game, string $target, ?string $zone = null): ?array;

    public function getStock(): float;
}
