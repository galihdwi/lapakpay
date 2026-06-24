<?php

namespace app\repositories;

use app\models\Transaction;

class TransactionRepository
{
    public function findById($id): ?Transaction
    {
        return Transaction::findOne($id);
    }

    public function findByInvoiceNumber(?string $invoiceNumber): ?Transaction
    {
        if ($invoiceNumber === null || $invoiceNumber === '') {
            return null;
        }

        return Transaction::findOne(['invoice_number' => $invoiceNumber]);
    }

    public function latest(int $limit = 10): array
    {
        return Transaction::find()->orderBy(['created_at' => SORT_DESC])->limit($limit)->all();
    }

    public function save(Transaction $transaction, bool $runValidation = true, ?array $attributes = null): bool
    {
        return $transaction->save($runValidation, $attributes);
    }
}
