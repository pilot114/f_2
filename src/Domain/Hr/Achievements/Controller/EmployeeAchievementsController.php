<?php

declare(strict_types=1);

namespace App\Domain\Hr\Achievements\Controller;

use App\Common\Attribute\RpcMethod;
use App\Common\Attribute\RpcParam;
use App\Common\DTO\FindResponse;
use App\Domain\Hr\Achievements\DTO\AchievementEmployeeItemResponse;
use App\Domain\Hr\Achievements\DTO\AchievementEmployeeItemWithoutAchievementResponse;
use App\Domain\Hr\Achievements\DTO\UnlockFromExcelResponse;
use App\Domain\Hr\Achievements\Entity\AchievementEmployeeItem;
use App\Domain\Hr\Achievements\UseCase\EmployeeAchievementExcelUseCase;
use App\Domain\Hr\Achievements\UseCase\EmployeeAchievementsUseCase;
use DateTimeImmutable;

class EmployeeAchievementsController
{
    public function __construct(
        private EmployeeAchievementsUseCase     $useCase,
        private EmployeeAchievementExcelUseCase $excelUseCase,
    ) {
    }

    /**
     * @return FindResponse<AchievementEmployeeItemResponse>
     */
    #[RpcMethod(
        'hr.achievements.getEmployeeAchievements',
        'Получить достижения сотрудников',
    )]
    public function getAll(): FindResponse
    {
        $items = $this->useCase->getAchievementEmployeeItems()
            ->map(fn (AchievementEmployeeItem $x): AchievementEmployeeItemResponse => $x->toAchievementEmployeeItemResponse())
        ;
        return new FindResponse($items);
    }

    #[RpcMethod(
        'hr.achievements.unlockEmployeeAchievements',
        'Назначить сотруднику достижение',
        examples: [
            [
                'summary' => 'Присвоить пользователю 123 достижение 4',
                'params'  => [
                    'achievementId' => 4,
                    'userId'        => 3,
                    'receiveDate'   => '2025-09-02T00:00:00+00:00',
                ],
            ],
        ],
    )]
    public function unlock(
        #[RpcParam('Id достижения')] int $achievementId,
        #[RpcParam('Id пользователя')] int $userId,
        #[RpcParam('Дата получения')] DateTimeImmutable $receiveDate,
    ): AchievementEmployeeItemResponse {
        return $this->useCase
            ->unlockAchievement($achievementId, $userId, $receiveDate)
            ->toAchievementEmployeeItemResponse()
        ;
    }

    #[RpcMethod(
        'hr.achievements.deleteEmployeeAchievements',
        'Отменить достижение сотрудника',
        examples: [
            [
                'summary' => 'Отменить запись о достижении №33',
                'params'  => [
                    'recordId' => 33,
                ],
            ],
        ],
    )]
    public function delete(#[RpcParam('Id достижения сотрудника')] int $recordId): bool
    {
        return $this->useCase->deleteAchievement($recordId);
    }

    #[RpcMethod(
        'hr.achievements.unlockFromExcel',
        'Массовое присвоение достижений из файла',
        examples: [
            [
                'summary' => 'Назначить достижения по данным файла #7334',
                'params'  => [
                    'fileId' => 7334,
                    'cardId' => 7334,
                ],
            ],
        ],
    )]
    public function unlockFromExcel(#[RpcParam('Id файла')] int $fileId, #[RpcParam('Id достижения')] int $cardId): UnlockFromExcelResponse
    {
        return new UnlockFromExcelResponse(...$this->excelUseCase->unlockFromExcel($fileId, $cardId));
    }

    /**
     * @return FindResponse<AchievementEmployeeItemWithoutAchievementResponse>
     */
    #[RpcMethod(
        'hr.achievements.getAchievementUnlockers',
        'Получить список назначений сотрудникам определённого достижения',
        examples: [
            [
                'summary' => 'Получить список сотрудников с достижением #34',
                'params'  => [
                    'achievementId' => 34,
                ],
            ],
        ],
    )]
    public function getAchievementUnlockers(#[RpcParam('Id достижения')] int $achievementId): FindResponse
    {
        $data = $this->useCase->getAchievementUnlockers($achievementId)
            ->map(fn (AchievementEmployeeItem $item): AchievementEmployeeItemWithoutAchievementResponse => $item->toAchievementEmployeeItemWithoutAchievementResponse())
        ;
        return new FindResponse($data);
    }

    #[RpcMethod(
        'hr.achievements.editEmployeeAchievement',
        'Редактировать достижение. Необходимо указать номер записи(id) и хотя бы один параметр для редактирования.',
        examples: [
            [
                'summary' => 'Редактировать запись №42',
                'params'  => [
                    'id'            => 42,
                    'achievementId' => 4,
                    'userId'        => 3,
                    'receiveDate'   => '2025-01-21',
                ],
            ],
        ],
    )]
    public function editAchievementRecord(
        #[RpcParam('Номер записи')] int                                  $id,
        #[RpcParam(summary: 'id пользователя', required: false)] ?int    $userId = null,
        #[RpcParam(summary: 'id достижения', required: false)] ?int      $achievementId = null,
        #[RpcParam(summary: 'Дата получения', required: false)] ?DateTimeImmutable  $receiveDate = null,
    ): AchievementEmployeeItemResponse {
        return $this->useCase
            ->editAchievementRecord($id, $userId, $achievementId, $receiveDate)
            ->toAchievementEmployeeItemResponse();
    }
}
