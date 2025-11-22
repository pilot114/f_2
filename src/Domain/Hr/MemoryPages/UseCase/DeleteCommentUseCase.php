<?php

declare(strict_types=1);

namespace App\Domain\Hr\MemoryPages\UseCase;

use App\Domain\Hr\MemoryPages\MemoryPagePhotoService;
use App\Domain\Hr\MemoryPages\Repository\CommentCommandRepository;
use App\Domain\Hr\MemoryPages\Repository\CommentsQueryRepository;
use App\Domain\Portal\Security\Entity\SecurityUser;
use App\Domain\Portal\Security\Repository\SecurityQueryRepository;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class DeleteCommentUseCase
{
    public function __construct(
        private CommentsQueryRepository $commentsQueryRepository,
        private CommentCommandRepository $commentCommandRepository,
        private MemoryPagePhotoService  $photoService,
        private SecurityQueryRepository $secRepo,
        private SecurityUser $securityUser,
    ) {
    }

    public function deleteComment(int $commentId): void
    {
        $comment = $this->commentsQueryRepository->getById($commentId);

        $hasAccess = $this->secRepo->hasCpAction($this->securityUser->id, 'memory_pages.memory_pages_add');
        if (!$hasAccess && $this->securityUser->id !== $comment->getEmployee()->id) {
            throw new AccessDeniedHttpException("Комментарий может удалить только автор, либо сотрудник с соответствующими правами");
        }

        $commentPhotos = $this->photoService->getCommentPhotos($commentId);

        $this->commentCommandRepository->delete($comment->getId());

        foreach ($commentPhotos as $photo) {
            $this->photoService->commonDelete($photo->getId(), $photo->getUserId());
        }
    }
}
