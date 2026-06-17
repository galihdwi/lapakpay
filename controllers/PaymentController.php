<?php

namespace app\controllers;

use yii\web\Controller;

class PaymentController extends Controller
{
    public function actionSuccess(?string $id = null): string
    {
        return $this->render('success', [
            'invoiceNumber' => $id,
        ]);
    }

    public function actionFailure(?string $id = null): string
    {
        return $this->render('failure', [
            'invoiceNumber' => $id,
        ]);
    }
}
