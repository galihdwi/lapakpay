<?php

namespace app\jobs;

use Yii;
use yii\base\BaseObject;
use yii\queue\JobInterface;

class SupplierOrderJob extends BaseObject implements JobInterface
{
    public $transaction_id;

    public function execute($queue)
    {
        Yii::createObject(\app\services\OrderService::class)
            ->sendTransactionToSupplier((string) $this->transaction_id);
    }
}
