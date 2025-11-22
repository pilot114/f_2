<?php

declare(strict_types=1);

namespace App\Domain\Hr\MemoryPages;

use App\Common\Service\File\FileService;
use App\Domain\Portal\Files\Entity\File;
use Illuminate\Support\Enumerable;

class MemoryPagePhotoService extends FileService
{
    public const MAIN_IMAGE_COLLECTION = 'cp_mp_main';
    public const OTHER_IMAGE_COLLECTION = 'cp_mp_other';
    public const COMMENTS_IMAGE_COLLECTION = 'cp_mp_comments';
    public const USER_AVATAR_COLLECTION = 'userpic';

    /** @return Enumerable<int, File> */
    public function getCommentPhotos(int $commentId): Enumerable
    {
        return $this->readRepo->findBy([
            'parentid'   => $commentId,
            'parent_tbl' => self::COMMENTS_IMAGE_COLLECTION,
        ]);
    }

    public function getAvatar(int $userId): ?File
    {
        return $this->readRepo->findOneBy([
            'parentid'   => $userId,
            'parent_tbl' => self::USER_AVATAR_COLLECTION,
        ]);
    }
}
