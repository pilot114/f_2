<?php

declare(strict_types=1);

namespace App\Common\Service\File;

use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Для безопасного создания временных файлов (удаляются после выполнения HTTP запроса)
 */
class TempFileRegistry
{
    private array $paths = [];

    public function createFile(string $content = ''): File
    {
        return new File($this->getTempPath($content));
    }

    /**
     * UploadedFile также имеет читаемое имя, в отличие от File
     */
    public function createUploadedFile(string $readableName, string $content = ''): UploadedFile
    {
        return new UploadedFile($this->getTempPath($content), $readableName, test: true);
    }

    public function clear(): void
    {
        foreach ($this->paths as $path) {
            unlink($path);
        }
        $this->paths = [];
    }

    private function getTempPath(string $content): string
    {
        $tempFilePath = sys_get_temp_dir() . '/' . uniqid();
        if (file_put_contents($tempFilePath, $content) === false) {
            throw new IOException('Не удалось сохранить временный файл', path: $tempFilePath);
        }
        $this->paths[] = $tempFilePath;
        return $tempFilePath;
    }
}
