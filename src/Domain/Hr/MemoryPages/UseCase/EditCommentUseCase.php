<?php

declare(strict_types=1);

namespace App\Domain\Hr\MemoryPages\UseCase;

use App\Common\Service\File\ImageBase64;
use App\Domain\Hr\MemoryPages\DTO\EditCommentRequest;
use App\Domain\Hr\MemoryPages\DTO\Photo;
use App\Domain\Hr\MemoryPages\Entity\Comment;
use App\Domain\Hr\MemoryPages\MemoryPagePhotoService;
use App\Domain\Hr\MemoryPages\Repository\CommentCommandRepository;
use App\Domain\Hr\MemoryPages\Repository\CommentsQueryRepository;
use App\Domain\Portal\Files\Entity\File;
use App\Domain\Portal\Security\Entity\SecurityUser;
use App\Domain\Portal\Security\Repository\SecurityQueryRepository;
use Database\Connection\TransactionInterface;
use DomainException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class EditCommentUseCase
{
    public function __construct(
        private CommentsQueryRepository $commentsQueryReposytory,
        private CommentCommandRepository $commentCommandRepository,
        private MemoryPagePhotoService $photoService,
        private ImageBase64 $imageBase64,
        private TransactionInterface $transaction,
        private SecurityQueryRepository $secRepo,
        private SecurityUser $securityUser,
    ) {
    }

    public function editComment(EditCommentRequest $request): Comment
    {
        $comment = $this->commentsQueryReposytory->getById($request->commentId);
        $employee = $comment->getEmployee();

        $hasAccess = $this->secRepo->hasCpAction($this->securityUser->id, 'memory_pages.memory_pages_add');
        if (!$hasAccess && $this->securityUser->id !== $employee->id) {
            throw new AccessDeniedHttpException("Комментарий может редактировать только автор, либо сотрудник с соответствующими правами");
        }

        if (($avatar = $this->photoService->getAvatar($employee->id)) instanceof File) {
            $employee->setAvatar($avatar);
        }

        $commentPhotos = $this->photoService->getCommentPhotos($request->commentId);

        foreach ($commentPhotos as $photo) {
            $comment->addPhoto($photo);
        }

        $this->validatePhotosCount($comment, $request->photos);
        $comment->setText($request->text);

        $this->transaction->beginTransaction();
        if ($request->photos !== []) {
            $this->changePhotos($comment, $request->photos);
        }

        $this->commentCommandRepository->update($comment);
        $this->transaction->commit();

        return $comment;
    }

    /** @param Photo[] $photos */
    private function changePhotos(Comment $comment, array $photos): void
    {
        foreach ($photos as $photoDto) {
            if ($photoDto->id && $photoDto->toDelete) {
                $photo = $comment->getPhotoById($photoDto->id);
                if (!$photo instanceof File) {
                    throw new NotFoundHttpException("фотография с id = {$photoDto->id} не найдена в коллекции фото");
                }

                $this->photoService->commonDelete($photo->getId(), $photo->getUserId());
                $comment->removePhotoPhotos($photo->getId());
            }

            if ($photoDto->base64 && $photoDto->id && !$photoDto->toDelete) {
                $photo = $comment->getPhotoById($photoDto->id);
                if (!$photo instanceof File) {
                    throw new NotFoundHttpException("фотография с id = {$photoDto->id} не найдена в коллекции фото");
                }

                $tempPhoto = $this->imageBase64->baseToFile($photoDto->base64);
                $this->photoService->commonDelete($photo->getId(), $photo->getUserId());
                $comment->removePhotoPhotos($photo->getId());
                $newPhoto = $this->photoService->commonUpload(
                    $tempPhoto,
                    MemoryPagePhotoService::COMMENTS_IMAGE_COLLECTION,
                    null,
                    $comment->getId(),
                );
                $comment->addPhoto($newPhoto);
            }

            if ($photoDto->base64 && !$photoDto->id && !$photoDto->toDelete) {
                $tempPhoto = $this->imageBase64->baseToFile($photoDto->base64);
                $newOtherPhoto = $this->photoService->commonUpload(
                    $tempPhoto,
                    MemoryPagePhotoService::COMMENTS_IMAGE_COLLECTION,
                    null,
                    $comment->getId(),
                );
                $comment->addPhoto($newOtherPhoto);
            }
        }
    }

    /** @param Photo[] $photos */
    private function validatePhotosCount(Comment $comment, array $photos): void
    {
        $photosToDelete = count(array_filter($photos, fn (Photo $photo): bool => $photo->toDelete));
        $photosToAdd = count(array_filter($photos, fn (Photo $photo): bool => $photo->toDelete === false && !isset($photo->id)));
        $currentPhotosCount = count($comment->getPhotos());
        $resultPhotosCount = $currentPhotosCount + $photosToAdd - $photosToDelete;
        ;
        if ($resultPhotosCount > 10) {
            throw new DomainException("не должно быть больше 10 дополнительных фото");
        }
    }
}
