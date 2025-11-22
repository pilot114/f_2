<?php

declare(strict_types=1);

namespace App\Domain\Hr\MemoryPages\UseCase;

use App\Common\Service\File\ImageBase64;
use App\Domain\Hr\MemoryPages\DTO\AddCommentRequest;
use App\Domain\Hr\MemoryPages\Entity\Comment;
use App\Domain\Hr\MemoryPages\Entity\Employee;
use App\Domain\Hr\MemoryPages\MemoryPagePhotoService;
use App\Domain\Hr\MemoryPages\Repository\CommentCommandRepository;
use App\Domain\Hr\MemoryPages\Repository\MemoryPageQueryRepository;
use App\Domain\Portal\Files\Entity\File;
use App\Domain\Portal\Security\Entity\SecurityUser;
use Database\ORM\Attribute\Loader;
use DateTimeImmutable;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AddCommentUseCase
{
    public function __construct(
        private CommentCommandRepository  $commentCommandRepository,
        private MemoryPageQueryRepository $memoryPageQueryRepository,
        private SecurityUser              $currentUser,
        private ImageBase64               $imageBase64,
        private MemoryPagePhotoService    $photoService,
    ) {
    }

    public function addComment(AddCommentRequest $request): Comment
    {
        $memoryPageExists = $this->memoryPageQueryRepository->count([
            'id' => $request->memoryPageId,
        ]);
        if ($memoryPageExists < 1) {
            throw new NotFoundHttpException("не найдена страница памяти с id = {$request->memoryPageId}");
        }
        $avatar = $this->photoService->getAvatar($this->currentUser->id);
        $employee = new Employee($this->currentUser->id, $this->currentUser->name);
        if ($avatar instanceof File) {
            $employee->setAvatar($avatar);
        }
        $comment = new Comment(
            id: Loader::ID_FOR_INSERT,
            memoryPageId: $request->memoryPageId,
            isPinned: false,
            createDate: new DateTimeImmutable(),
            employee: $employee,
            text: $request->text
        );

        $this->commentCommandRepository->create($comment);

        foreach ($request->photos as $photoDto) {
            if ($photoDto->base64) {
                $tempPhoto = $this->imageBase64->baseToFile($photoDto->base64);
                $photo = $this->photoService->commonUpload(
                    $tempPhoto,
                    MemoryPagePhotoService::COMMENTS_IMAGE_COLLECTION,
                    null,
                    $comment->getId(),
                );
                $comment->addPhoto($photo);
            }
        }

        return $comment;
    }
}
