<?php

declare(strict_types=1);

namespace App\Domain\Portal\Cabinet\UseCase;

use App\Common\Service\Integration\RpcClient;
use App\Domain\Portal\Cabinet\DTO\WorkTime as WorkTimeDto;
use App\Domain\Portal\Cabinet\Entity\Profile;
use App\Domain\Portal\Cabinet\Entity\WorkTime;
use App\Domain\Portal\Cabinet\Repository\ProfileCommandRepository;
use App\Domain\Portal\Cabinet\Repository\ProfileQueryRepository;
use App\Domain\Portal\Cabinet\Repository\WorkTimeCommandRepository;
use Database\Connection\TransactionInterface;
use Database\ORM\Attribute\Loader;
use DateTimeImmutable;

class ChangeProfileInfoUseCase
{
    public function __construct(
        private ProfileCommandRepository $write,
        private ProfileQueryRepository $read,
        private WorkTimeCommandRepository $writeWorkTime,
        private RpcClient $rpcClient,
        private TransactionInterface $transaction
    ) {
    }

    public function changeProfileInfo(
        int $userId,
        ?bool $hideBirthday,
        ?string $telegram,
        ?string $phone,
        ?string $city,
        ?WorkTimeDto $workTimeDto,
    ): Profile {
        $profile = $this->read->getProfileByUserId($userId);

        if (is_bool($hideBirthday)) {
            $profile->setHideBirthday($hideBirthday);
        }

        $profile->setTelegram($telegram);
        $profile->setPhone($phone);
        $profile->setCity($city);

        $this->transaction->beginTransaction();
        $this->write->updateInfo($profile);

        if ($workTimeDto instanceof WorkTimeDto) {
            $this->createOrUpdateWorkTime($profile, $workTimeDto);
        }

        $this->transaction->commit();

        $this->rpcClient->call(
            'Department.editCachedUserData',
            [
                'userId'     => $profile->getUserId(),
                'parameters' => [
                    'telegram'          => $profile->getTelegram(),
                    'office_phone_city' => $profile->getPhone(),
                    'work_address'      => $profile->getCity(),
                    'userpic'           => $profile->getAvatarImages()['small'],
                    'userpic_big'       => $profile->getAvatarImages()['large'],
                    'work_time'         => $profile->getWorkTime() instanceof WorkTime ? [
                        'start'    => $profile->getWorkTime()->getStart()->format(DateTimeImmutable::ATOM),
                        'end'      => $profile->getWorkTime()->getEnd()->format(DateTimeImmutable::ATOM),
                        'timeZone' => [
                            'value' => $profile->getWorkTime()->getTimeZone()->value,
                            'title' => $profile->getWorkTime()->getTimeZone()->getTitle(),
                        ],
                    ] : null,
                ],
            ]
        );

        return $profile;
    }

    private function createOrUpdateWorkTime(Profile $profile, WorkTimeDto $workTimeDto): void
    {
        $workTime = $profile->getWorkTime();
        if ($workTime instanceof WorkTime) {
            $workTime->updateTime($workTimeDto->start, $workTimeDto->end, $workTimeDto->timeZone);
            $this->writeWorkTime->updateWorkTime($workTime);
        } else {
            $workTime = new WorkTime(
                id: Loader::ID_FOR_INSERT,
                userId: $profile->getUserId(),
                start: $workTimeDto->start,
                end: $workTimeDto->end,
                timeZone: $workTimeDto->timeZone
            );
            $this->writeWorkTime->create($workTime);
            $profile->setWorkTime($workTime);
        }
    }
}
