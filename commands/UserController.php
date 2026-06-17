<?php

declare(strict_types=1);

namespace app\commands;

use app\models\User;
use app\repositories\UserRepository;
use yii\console\Controller;
use yii\console\ExitCode;

class UserController extends Controller
{
    public function __construct(
        $id,
        $module,
        private readonly UserRepository $userRepository,
        $config = [],
    ) {
        parent::__construct($id, $module, $config);
    }

    public function actionCreateAdmin(string $username, string $email, string $password): int
    {
        $user = $this->userRepository->findByUsername($username) ?: new User();
        $user->setAttributes([
            'username' => $username,
            'email' => $email,
            'role' => 'admin',
            'balance' => 0,
            'status' => 'active',
        ], false);
        $user->setPassword($password);
        $user->generateAuthKey();

        if (!$this->userRepository->save($user)) {
            foreach ($user->getFirstErrors() as $error) {
                $this->stderr($error . PHP_EOL);
            }

            return ExitCode::DATAERR;
        }

        $this->stdout("Admin user siap: {$username}" . PHP_EOL);

        return ExitCode::OK;
    }
}
