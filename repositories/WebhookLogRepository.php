<?php

namespace app\repositories;

use app\models\WebhookLog;

class WebhookLogRepository
{
    public function create(
        string $provider,
        array $payload,
        array $headers,
        string $event,
        string $status = 'received',
        ?string $notes = null,
    ): WebhookLog {
        $log = new WebhookLog([
            'provider' => $provider,
            'event' => $event,
            'payload' => $payload,
            'headers' => $headers,
            'status' => $status,
            'notes' => $notes,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $log->save(false);

        return $log;
    }
}
