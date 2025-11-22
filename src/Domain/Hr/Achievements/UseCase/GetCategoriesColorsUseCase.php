<?php

declare(strict_types=1);

namespace App\Domain\Hr\Achievements\UseCase;

use App\Common\Service\File\FileService;
use App\Domain\Hr\Achievements\Entity\Color;
use App\Domain\Portal\Files\Entity\File;
use Database\ORM\QueryRepositoryInterface;
use Illuminate\Support\Enumerable;

class GetCategoriesColorsUseCase
{
    /**
     * @param QueryRepositoryInterface<Color> $repository
     */
    public function __construct(
        private QueryRepositoryInterface $repository,
        private FileService              $fileService
    ) {
    }

    /** @return Enumerable<int, Color> */
    public function getColors(): Enumerable
    {
        $data = $this->repository->findAll();
        foreach ($data as $item) {
            $file = $this->fileService->getById($item->getFileId());
            if (!$file instanceof File) {
                continue;
            }
            $url = $this->fileService->getStaticUrl($file);
            $item->setFile($file->getId(), $url);
        }
        return $data;
    }
}
