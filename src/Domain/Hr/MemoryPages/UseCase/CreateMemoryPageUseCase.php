<?php

declare(strict_types=1);

namespace App\Domain\Hr\MemoryPages\UseCase;

use App\Common\Service\File\ImageBase64;
use App\Domain\Hr\MemoryPages\DTO\CreateMemoryPageRequest;
use App\Domain\Hr\MemoryPages\Entity\MemoryPage;
use App\Domain\Hr\MemoryPages\Entity\WorkPeriod;
use App\Domain\Hr\MemoryPages\MemoryPagePhotoService;
use App\Domain\Hr\MemoryPages\Repository\EmployeeQueryRepository;
use App\Domain\Hr\MemoryPages\Repository\MemoryPageCommandRepository;
use App\Domain\Hr\MemoryPages\Repository\ResponsesQueryRepository;
use App\Domain\Hr\MemoryPages\Repository\WorkPeriodsCommandRepository;
use Database\Connection\TransactionInterface;
use Database\ORM\Attribute\Loader;
use DateTimeImmutable;
use InvalidArgumentException;

class CreateMemoryPageUseCase
{
    public function __construct(
        private MemoryPageCommandRepository $memoryPageCommandRepository,
        private EmployeeQueryRepository $employeeQueryRepository,
        private ResponsesQueryRepository $responsesQueryRepository,
        private WorkPeriodsCommandRepository $workPeriodsCommandRepository,
        private TransactionInterface $transaction,
        private ImageBase64 $imageBase64,
        private MemoryPagePhotoService $photoService
    ) {
    }

    public function create(CreateMemoryPageRequest $request): MemoryPage
    {
        $this->transaction->beginTransaction();
        $employee = $this->employeeQueryRepository->findOrFail($request->employeeId, "не найден сотрудник");
        $memoryPage = new MemoryPage(
            id: Loader::ID_FOR_INSERT,
            employee: $employee,
            birthDate: $request->birthDate,
            deathDate: $request->deathDate,
            createDate: new DateTimeImmutable(),
            obituary: $request->obituary,
            obituaryFull: $request->obituaryFull,
        );
        $this->memoryPageCommandRepository->create($memoryPage);

        foreach ($request->workPeriods as $workPeriodDto) {
            if (!$workPeriodDto->responseId || !$workPeriodDto->startDate || !$workPeriodDto->endDate) {
                throw new InvalidArgumentException('при создании периодов работы должны быть указаны все параметры');
            }
            $response = $this->responsesQueryRepository->findOrFail($workPeriodDto->responseId, "нe найдена должность");
            $workPeriod = new WorkPeriod(
                id: Loader::ID_FOR_INSERT,
                memoryPageId: $memoryPage->getId(),
                startDate: $workPeriodDto->startDate,
                endDate: $workPeriodDto->endDate,
                response: $response,
                achievements: $workPeriodDto->achievements
            );
            $this->workPeriodsCommandRepository->create($workPeriod);

            $memoryPage->addWorkPeriod($workPeriod);
        }

        $tempMainPagePhoto = $this->imageBase64->baseToFile($request->mainPhotoBase64);
        $mainPagePhoto = $this->photoService->commonUpload(
            $tempMainPagePhoto,
            MemoryPagePhotoService::MAIN_IMAGE_COLLECTION,
            null,
            $memoryPage->getId(),
        );

        $memoryPage->setMainPhoto($mainPagePhoto);

        foreach ($request->otherPhotos as $otherPhotoDto) {
            if ($otherPhotoDto->base64) {
                $tempOtherPagePhoto = $this->imageBase64->baseToFile($otherPhotoDto->base64);
                $otherPagePhoto = $this->photoService->commonUpload(
                    $tempOtherPagePhoto,
                    MemoryPagePhotoService::OTHER_IMAGE_COLLECTION,
                    null,
                    $memoryPage->getId(),
                );
                $memoryPage->addOtherPhoto($otherPagePhoto);
            }
        }
        $this->transaction->commit();

        return $memoryPage;
    }
}
