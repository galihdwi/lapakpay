<?php

namespace app\providers;

use app\interfaces\ProviderInterface;
use yii\base\Component;
use yii\httpclient\Client;

class VipResellerProvider extends Component implements ProviderInterface
{
    public string $apiUrl = '';
    public string $apiId = '';
    public string $apiKey = '';

    private Client $client;

    public function init(): void
    {
        parent::init();
        $this->client = new Client([
            'baseUrl' => $this->apiUrl,
        ]);
    }

    public function getServices(): array
    {
        return $this->sendRequest('game-feature', ['type' => 'services']);
    }

    public function order(string $serviceCode, string $target, ?string $zone = null, ?string $externalId = null): array
    {
        return $this->sendRequest('game-feature', [
            'type' => 'order',
            'service' => $serviceCode,
            'target' => $target,
            'zone' => $zone,
            'ext_id' => $externalId,
        ]);
    }

    public function checkStatus(string $trxId): array
    {
        return $this->sendRequest('game-feature', [
            'type' => 'status',
            'trxid' => $trxId,
        ]);
    }

    public function getNickname(string $game, string $target, ?string $zone = null): ?array
    {
        return $this->sendRequest('game-feature', [
            'type' => 'get-nickname',
            'game' => $game,
            'target' => $target,
            'zone' => $zone,
        ]);
    }

    public function getStock(): float
    {
        $response = $this->sendRequest('profile');

        return (float) ($response['data']['balance'] ?? 0);
    }

    private function sendRequest(string $endpoint, array $data = []): array
    {
        $data['key'] = $this->apiKey;
        $data['sign'] = md5($this->apiId . $this->apiKey);

        $response = $this->client->post($endpoint, $data)->send();

        if (!$response->isOk) {
            throw new \RuntimeException('VIP Reseller API Error: ' . $response->content);
        }

        return $response->data;
    }
}
