<?php

namespace app\components;

use yii\bootstrap5\NavBar as BootstrapNavBar;
use yii\helpers\Html;

class NavBar extends BootstrapNavBar
{
    /**
     * @var bool whether to encode the brand label
     */
    public $encodeBrand = true;

    public function init()
    {
        if ($this->brandLabel !== false && $this->encodeBrand) {
            $this->brandLabel = Html::encode($this->brandLabel);
        }
        parent::init();
    }
}
