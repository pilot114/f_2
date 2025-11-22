<?php

declare(strict_types=1);

namespace App\Domain\Hr\MemoryPages\UseCase;

use App\Domain\Hr\MemoryPages\DTO\PinCommentRequest;
use App\Domain\Hr\MemoryPages\Entity\Comment;
use App\Domain\Hr\MemoryPages\MemoryPagePhotoService;
use App\Domain\Hr\MemoryPages\Repository\CommentCommandRepository;
use App\Domain\Hr\MemoryPages\Repository\CommentsQueryRepository;
use App\Domain\Portal\Files\Entity\File;

class PinCommentUseCase
{
    public function __construct(
        private CommentCommandRepository $commentCommandRepository,
        private CommentsQueryRepository $commentsQueryRepository,
        private MemoryPagePhotoService $photoService,
    ) {
    }

    public function togglePinned(PinCommentRequest $request): Comment
    {
        $comment = $this->commentsQueryRepository->getById($request->commentId);
        $commentPhotos = $this->photoService->getCommentPhotos($request->commentId);

        foreach ($commentPhotos as $photo) {
            $comment->addPhoto($photo);
        }

        $comment->setIsPinned($request->isPinned);
        $employee = $comment->getEmployee();
        if (($avatar = $this->photoService->getAvatar($employee->id)) instanceof File) {
            $employee->setAvatar($avatar);
        }

        $this->commentCommandRepository->update($comment);

        return $comment;
    }

}
