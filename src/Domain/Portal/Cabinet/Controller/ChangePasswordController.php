<?php

declare(strict_types=1);

namespace App\Domain\Portal\Cabinet\Controller;

use App\Common\Attribute\RpcMethod;
use App\Common\Attribute\RpcParam;
use App\Domain\Portal\Cabinet\UseCase\ChangePasswordUseCase;
use App\Domain\Portal\Security\Entity\SecurityUser;
use Symfony\Component\Validator\Constraints as Assert;

class ChangePasswordController
{
    public function __construct(
        private ChangePasswordUseCase $useCase,
        private SecurityUser $currentUser,
    ) {
    }

    #[RpcMethod(
        'portal.cabinet.changePassword',
        'изменение пароля пользователя',
        examples: [
            [
                'summary' => 'изменение пароля пользователя',
                'params'  => [
                    'oldPassword' => 'old-password',
                    'newPassword' => 'new-password',
                ],
            ],
        ],
    )]
    public function __invoke(
        #[RpcParam('Старый пароль')]
        #[Assert\NotBlank]
        string $oldPassword,

        #[RpcParam('Новый пароль')]
        #[Assert\NotBlank(message: 'Пароль не может быть пустым')]
        #[Assert\Length(
            min: 8,
            max: 20,
            minMessage: 'Длина пароля должна быть не менее {{ limit }} символов',
            maxMessage: 'Длина пароля должна быть не более {{ limit }} символов',
        )]
        #[Assert\Regex(
            pattern: '/[A-Z]/',
            message: 'В пароле должна быть хотя бы одна большая буква английского алфавита от A до Z'
        )]
        #[Assert\Regex(
            pattern: '/[a-z]/',
            message: 'В пароле должна быть хотя бы одна маленькая буква английского алфавита от a до z'
        )]
        #[Assert\Regex(
            pattern: '/\d/',
            message: 'В пароле должна быть хотя бы одна цифра (от 0 до 9)'
        )]
        #[Assert\Regex(
            pattern: '/[^A-Za-z0-9]/',
            message: 'В пароле должен быть хотя бы один спецсимвол'
        )]
        string $newPassword,
    ): void {
        $this->useCase->changePassword($this->currentUser->id, $oldPassword, $newPassword);
    }
}
