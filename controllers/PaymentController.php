<?php

namespace app\controllers;

use app\repositories\TransactionRepository;
use yii\web\Controller;

class PaymentController extends Controller
{
    public function __construct(
        $id,
        $module,
        private readonly TransactionRepository $transactionRepository,
        $config = [],
    ) {
        parent::__construct($id, $module, $config);
    }

    public function actionSuccess(?string $id = null): string
    {
        $transaction = $this->transactionRepository->findByInvoiceNumber($id);

        return $this->render('success', [
            'invoiceNumber' => $id,
            'transaction' => $transaction,
        ]);
    }

    public function actionFailure(?string $id = null): string
    {
        $transaction = $this->transactionRepository->findByInvoiceNumber($id);

        return $this->render('failure', [
            'invoiceNumber' => $id,
            'transaction' => $transaction,
        ]);
    }
}
