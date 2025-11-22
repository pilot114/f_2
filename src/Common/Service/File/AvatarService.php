<?php

declare(strict_types=1);

namespace App\Common\Service\File;

use App\Domain\Portal\Files\Entity\File;

class AvatarService extends FileService
{
    public function getAvatar(int $userId): ?File
    {
        return $this->readRepo->findOneBy([
            'parentid'   => $userId,
            'parent_tbl' => File::USERPIC_COLLECTION,
        ]);
    }
}
