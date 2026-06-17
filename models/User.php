<?php

namespace app\models;

use Yii;
use yii\mongodb\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * User model for MongoDB.
 *
 * @property \MongoDB\BSON\ObjectId $_id
 * @property string $username
 * @property string $email
 * @property string $password_hash
 * @property string $auth_key
 * @property string $role
 * @property float $balance
 * @property string $api_key
 * @property string $webhook_url
 * @property float $margin
 * @property string $status
 * @property string $created_at
 */
class User extends ActiveRecord implements IdentityInterface
{
    public string $password = '';

    public static function collectionName()
    {
        return 'users';
    }

    public function attributes()
    {
        return [
            '_id',
            'username',
            'email',
            'password_hash',
            'auth_key',
            'role',
            'balance',
            'api_key',
            'webhook_url',
            'margin',
            'status',
            'created_at',
        ];
    }

    public function rules()
    {
        return [
            [['username', 'email'], 'required'],
            [
                ['password'],
                'required',
                'when' => static fn (self $model): bool => $model->isNewRecord
                    && ($model->password_hash === null || $model->password_hash === ''),
            ],
            [['email'], 'email'],
            [['username', 'email'], 'unique'],
            [['balance', 'margin'], 'number'],
            [['balance', 'margin'], 'default', 'value' => 0],
            [['password_hash', 'api_key', 'webhook_url', 'password'], 'string'],
            [['role'], 'default', 'value' => 'user'],
            [['status'], 'default', 'value' => 'active'],
            [['created_at'], 'safe'],
        ];
    }

    public function beforeValidate()
    {
        if ($this->created_at === null || $this->created_at === '') {
            $this->created_at = date('Y-m-d H:i:s');
        }

        return parent::beforeValidate();
    }

    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['auth_key' => $token]);
    }

    public function getId()
    {
        return (string) $this->_id;
    }

    public function getAuthKey()
    {
        return $this->auth_key;
    }

    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username]);
    }

    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    public function getPasswordHash(): string
    {
        return (string) $this->password_hash;
    }

    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }
}
