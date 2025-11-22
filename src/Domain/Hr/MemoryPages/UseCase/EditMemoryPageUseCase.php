<?php

declare(strict_types=1);

namespace App\Domain\Hr\MemoryPages\UseCase;

use App\Common\Service\File\ImageBase64;
use App\Domain\Hr\MemoryPages\DTO\EditMemoryPageRequest;
use App\Domain\Hr\MemoryPages\DTO\GetMemoryPageRequest;
use App\Domain\Hr\MemoryPages\DTO\Photo;
use App\Domain\Hr\MemoryPages\DTO\WorkPeriod as WorkPeriodDto;
use App\Domain\Hr\MemoryPages\Entity\MemoryPage;
use App\Domain\Hr\MemoryPages\Entity\WorkPeriod;
use App\Domain\Hr\MemoryPages\MemoryPagePhotoService;
use App\Domain\Hr\MemoryPages\Repository\EmployeeQueryRepository;
use App\Domain\Hr\MemoryPages\Repository\MemoryPageCommandRepository;
use App\Domain\Hr\MemoryPages\Repository\MemoryPagePhotoQueryRepository;
use App\Domain\Hr\MemoryPages\Repository\MemoryPageQueryRepository;
use App\Domain\Hr\MemoryPages\Repository\ResponsesQueryRepository;
use App\Domain\Hr\MemoryPages\Repository\WorkPeriodsCommandRepository;
use App\Domain\Portal\Files\Entity\File;
use Database\Connection\TransactionInterface;
use Database\ORM\Attribute\Loader;
use Database\ORM\EntityTracker;
use DateTimeImmutable;
use DomainException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class EditMemoryPageUseCase
{
    public function __construct(
        private MemoryPageCommandRepository    $memoryPageCommandRepository,
        private EmployeeQueryRepository        $employeeQueryRepository,
        private ResponsesQueryRepository       $responsesQueryRepository,
        private WorkPeriodsCommandRepository   $workPeriodsCommandRepository,
        private MemoryPageQueryRepository      $memoryPageQueryRepository,
        private TransactionInterface           $transaction,
        private ImageBase64                    $imageBase64,
        private MemoryPagePhotoService         $photoService,
        private MemoryPagePhotoQueryRepository $photoRepository,
    ) {
    }

    public function edit(EditMemoryPageRequest $request): MemoryPage
    {
        $memoryPage = $this->memoryPageQueryRepository->getItem(new GetMemoryPageRequest($request->id));
        $photos = $this->photoRepository->getAllForMemoryPage($memoryPage);
        $memoryPage->setUpPhotos($photos);

        $this->validatePhotosCount($memoryPage, $request->otherPhotos);
        $this->validateWorkPeriodsCount($memoryPage, $request->workPeriods);

        if ($request->employeeId) {
            $employee = $this->employeeQueryRepository->findOrFail($request->employeeId, "на найден сотрудник");
            $memoryPage->setEmployee($employee);
        }
        if ($request->birthDate instanceof DateTimeImmutable) {
            $memoryPage->setBirthDate($request->birthDate);
        }
        if ($request->deathDate instanceof DateTimeImmutable) {
            $memoryPage->setDeathDate($request->deathDate);
        }
        if ($request->obituary) {
            $memoryPage->setObituary($request->obituary);
        }
        if ($request->obituaryFull) {
            $memoryPage->setObituaryFull($request->obituaryFull);
        }

        $this->transaction->beginTransaction();

        if ($request->mainPhotoBase64) {
            $this->changeMainPhoto($memoryPage, $request->mainPhotoBase64);
        }

        if ($request->otherPhotos !== []) {
            $this->changeOtherPhotos($memoryPage, $request->otherPhotos);
        }

        if ($request->workPeriods !== []) {
            $this->changeWorkPeriods($memoryPage, $request->workPeriods);
        }
        $this->memoryPageCommandRepository->update($memoryPage);
        $this->transaction->commit();

        return $memoryPage;
    }

    private function changeMainPhoto(MemoryPage $memoryPage, string $mainPhotoBase64): void
    {
        $tempMainPhoto = $this->imageBase64->baseToFile($mainPhotoBase64);
        $this->photoService->commonDelete($memoryPage->getMainPhoto()->getId(), $memoryPage->getMainPhoto()->getUserId());
        $newMainPhoto = $this->photoService->commonUpload(
            $tempMainPhoto,
            MemoryPagePhotoService::MAIN_IMAGE_COLLECTION,
            null,
            $memoryPage->getId(),
        );
        $memoryPage->setMainPhoto($newMainPhoto);
    }

    /** @param Photo[] $otherPhotos */
    private function validatePhotosCount(MemoryPage $memoryPage, array $otherPhotos): void
    {
        $photosToDelete = count(array_filter($otherPhotos, fn (Photo $photo): bool => $photo->toDelete));
        $photosToAdd = count(array_filter($otherPhotos, fn (Photo $photo): bool => $photo->toDelete === false && !isset($photo->id)));
        $currentPhotosCount = count($memoryPage->getOtherPhotos());
        $resultPhotosCount = $currentPhotosCount + $photosToAdd - $photosToDelete;
        ;
        if ($resultPhotosCount > 10) {
            throw new DomainException("не должно быть больше 10 дополнительных фото");
        }
    }

    /** @param WorkPeriodDto[] $workPeriods */
    private function validateWorkPeriodsCount(MemoryPage $memoryPage, array $workPeriods): void
    {
        $currentWorkPeriods = count($memoryPage->getWorkPeriods());
        $workPeriodsToDelete = count(array_filter($workPeriods, fn (WorkPeriodDto $workPeriod): bool => $workPeriod->toDelete));
        $workPeriodsToAdd = count(array_filter($workPeriods, fn (WorkPeriodDto $workPeriod): bool => $workPeriod->toDelete === false && !isset($workPeriod->id)));
        $workPeriodsResultCount = $currentWorkPeriods - $workPeriodsToDelete + $workPeriodsToAdd;
        if ($workPeriodsResultCount < 1) {
            throw new DomainException('должен быть хотя бы один период работы');
        }
    }

    /** @param Photo[] $otherPhotos */
    private function changeOtherPhotos(MemoryPage $memoryPage, array $otherPhotos): void
    {
        foreach ($otherPhotos as $otherPhoto) {
            if ($otherPhoto->id && $otherPhoto->toDelete) {
                $photo = $memoryPage->getOtherPhotoById($otherPhoto->id);
                if (!$photo instanceof File) {
                    throw new NotFoundHttpException("фотография с id = {$otherPhoto->id} не найдена в коллекции дополнительных фото");
                }

                $this->photoService->commonDelete($photo->getId(), $photo->getUserId());
                $memoryPage->removePhotoFromOtherPhotos($otherPhoto->id);
            }

            if ($otherPhoto->base64 && $otherPhoto->id && !$otherPhoto->toDelete) {
                $tempOtherPhoto = $this->imageBase64->baseToFile($otherPhoto->base64);
                $photo = $memoryPage->getOtherPhotoById($otherPhoto->id);
                if (!$photo instanceof File) {
                    throw new NotFoundHttpException("фотография с id = {$otherPhoto->id} не найдена в коллекции дополнительных фото");
                }

                $this->photoService->commonDelete($photo->getId(), $photo->getUserId());
                $memoryPage->removePhotoFromOtherPhotos($otherPhoto->id);
                $newOtherPhoto = $this->photoService->commonUpload(
                    $tempOtherPhoto,
                    MemoryPagePhotoService::OTHER_IMAGE_COLLECTION,
                    null,
                    $memoryPage->getId(),
                );
                $memoryPage->addOtherPhoto($newOtherPhoto);
            }

            if ($otherPhoto->base64 && !$otherPhoto->id && !$otherPhoto->toDelete) {
                $tempOtherPhoto = $this->imageBase64->baseToFile($otherPhoto->base64);
                $newOtherPhoto = $this->photoService->commonUpload(
                    $tempOtherPhoto,
                    MemoryPagePhotoService::OTHER_IMAGE_COLLECTION,
                    null,
                    $memoryPage->getId(),
                );
                $memoryPage->addOtherPhoto($newOtherPhoto);
            }
        }
    }

    /** @param WorkPeriodDto[] $workPeriods */
    private function changeWorkPeriods(MemoryPage $memoryPage, array $workPeriods): void
    {
        foreach ($workPeriods as $workPeriodDto) {
            $response = null;
            if ($workPeriodDto->responseId) {
                $response = $this->responsesQueryRepository->findOrFail($workPeriodDto->responseId, "нe найдена должность");
            }

            if ($workPeriodDto->id && $workPeriodDto->toDelete) {
                $this->workPeriodsCommandRepository->delete($workPeriodDto->id);
                $result = $memoryPage->removeWorkPeriod($workPeriodDto->id);
                if (!$result) {
                    throw new NotFoundHttpException('в коллекции рабочих периодов нет рабочего периода с id = ' . $workPeriodDto->id);
                }
            }

            if ($workPeriodDto->id && !$workPeriodDto->toDelete && $response && $workPeriodDto->startDate && $workPeriodDto->endDate) {
                $workPeriod = $memoryPage->getWorkPeriodById($workPeriodDto->id);
                if (!$workPeriod instanceof WorkPeriod) {
                    throw new NotFoundHttpException('в коллекции рабочих периодов нет рабочего периода с id = ' . $workPeriodDto->id);
                }
                // TODO: убрать EntityTracker отсюда. Нужно организовать добавление вложенных сущностей на уровне database
                EntityTracker::set($workPeriod);
                $workPeriod->setStartDate($workPeriodDto->startDate);
                $workPeriod->setEndDate($workPeriodDto->endDate);
                $workPeriod->setResponse($response);
                $workPeriod->setAchievements($workPeriodDto->achievements);
                $this->workPeriodsCommandRepository->update($workPeriod);
            }

            if (!$workPeriodDto->id && !$workPeriodDto->toDelete && $response && $workPeriodDto->startDate && $workPeriodDto->endDate) {
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
        }
    }
}
