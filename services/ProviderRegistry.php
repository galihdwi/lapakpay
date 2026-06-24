<?php

namespace app\services;

use app\interfaces\ProviderInterface;
use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;

class ProviderRegistry extends Component
{
    public function get(string $providerName): ProviderInterface
    {
        $componentId = Yii::$app->params['providers'][$providerName] ?? null;

        if ($componentId === null) {
            throw new InvalidConfigException("Provider '{$providerName}' is not registered.");
        }

        $provider = Yii::$app->get($componentId);

        if (!$provider instanceof ProviderInterface) {
            throw new InvalidConfigException("Provider '{$providerName}' must implement ProviderInterface.");
        }

        return $provider;
    }

    public function has(string $providerName): bool
    {
        return isset(Yii::$app->params['providers'][$providerName]);
    }
}
