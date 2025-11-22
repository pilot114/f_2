<?php

use App\System\DomainSourceCodeFinder;
use App\System\RefreshGenericsService;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\PropertyInfo\Extractor\PhpStanExtractor;

include __DIR__ . '/../vendor/autoload.php';

// Обновляет сервисы в config/generics.yaml
// Это нельзя сделать Symfony командой, так как команде нужен скомпилированный контейнер, а тут мы обновляем его конфиг

$projectDir = __DIR__ . '/..';

$refresher = new RefreshGenericsService(
    new PhpStanExtractor(),
    new DomainSourceCodeFinder(
        new Finder(),
        $projectDir
    ),
    new Filesystem(),
    $projectDir
);

$refresher->writeFile();
echo "Done\n";
