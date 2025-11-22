<?php

declare(strict_types=1);

namespace App\Domain\Hr\Achievements\UseCase;

use App\Common\Exception\FileException;
use App\Common\Service\File\FileService;
use App\Domain\Hr\Achievements\Repository\AchievementEmployeeItemCommandRepository;
use App\Domain\Hr\Achievements\Repository\AchievementEmployeeItemQueryRepository;
use App\Domain\Portal\Files\Entity\File;
use DateTimeImmutable;
use DateTimeInterface;
use InvalidArgumentException;
use OpenSpout\Common\Exception\OpenSpoutException;
use OpenSpout\Reader\XLSX\Options;
use OpenSpout\Reader\XLSX\Reader;
use Throwable;

class EmployeeAchievementExcelUseCase
{
    public function __construct(
        private FileService                              $service,
        private AchievementEmployeeItemQueryRepository   $readRepository,
        private AchievementEmployeeItemCommandRepository $writeRepository,
    ) {
    }

    protected function mapDatabaseError(Throwable $e): string
    {
        $message = $e->getMessage();
        $messageUC = strtoupper($message);
        if (str_contains($messageUC, 'TEST.CP_EA_EMPLOYEE_ACHIEVMENTS_FK_CP_EMP_ID')) {
            return 'Неверный ID пользователя';
        }
        if (str_contains($messageUC, 'CP_EA_EMPLOYEE_ACHIEVMENTS_FK_ACHIEVEMENT_CARDS_ID')) {
            return 'Неверный ID достижения.';
        }
        if (str_contains($messageUC, 'TEST.CP_EA_EMPLOYEE_ACHIEVMENTS_UQ_CEI_ACI_RD')) {
            return 'Такая запись о присвоении уже существует.';
        }
        return $message;
    }

    public function unlockFromExcel(int $fileId, int $cardId): array
    {
        $file = $this->service->getById($fileId);
        if (!$file instanceof File) {
            throw new FileException("Не найден файл с id $fileId!");
        }
        $filePath = $this->service->getStaticUrl($file);

        // Так как ридер может работать только с файлами и не умеет в протоколы
        $tmpName = tempnam(sys_get_temp_dir(), 'tmp');
        if (!$tmpName) {
            throw new FileException('Не удалось создать временный файл!');
        }
        $handle = fopen($tmpName, "w");
        if (!$handle) {
            throw new FileException('Не удалось открыть временный файл!');
        }
        $content = file_get_contents($filePath);
        if (!$content) {
            throw new FileException('Не удалось прочитать указанный файл!');
        }
        fwrite($handle, $content);
        fclose($handle);

        $readerOptions = new Options();
        $readerOptions->SHOULD_FORMAT_DATES = false; //Если тип данных в эксельке будет дата - сразу придет DateTimeImmutable
        $reader = new Reader($readerOptions);

        try {
            $reader->open($tmpName);
        } catch (OpenSpoutException $e) {
            throw new FileException("OpenSpout: невалидный excel файл с id = $fileId");
        }

        //Ошибки могут быть двух типов - невалидные данные и ошибка записи в бд
        $dataset = [];
        $errors = [];
        $total = 0;

        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $rowIndex => $row) {
                $rowIsEmpty = true;
                $tmp = [];
                foreach ($row->getCells() as $cell) {
                    $value = $cell->getValue();
                    if ($value === 'ФИО сотрудника') {
                        //тут нам надо пропусть строку - это заголовки.
                        continue 2;
                    }

                    // если есть хоть один непробельный символ — строка НЕ пустая
                    $sv = is_string($value) ? $value : null;
                    if ($sv !== null && preg_match('/\S/u', $sv) === 1) {
                        $rowIsEmpty = false;
                    }

                    $tmp[] = $value;
                }

                if ($rowIsEmpty) {
                    continue;
                }

                try { // Создание типа как валидация данных
                    $total++;
                    $empName = is_string($tmp[0]) ? $tmp[0] : '';
                    $user = $this->readRepository->findOneBy([
                        'name' => $empName,
                    ]);
                    if ($user === null) {
                        throw new InvalidArgumentException("Пользователь по имени '$empName' не найден.");
                    }
                    $date = gettype($tmp[1]) === 'string'
                        ? new DateTimeImmutable((string) preg_replace('/[^0-9\-]./', '', $tmp[1]))
                        : $tmp[1];
                    $date = $date instanceof DateTimeInterface
                        ? new DateTimeImmutable($date->format('Y-m-d')) : new DateTimeImmutable();
                    $dataset[] = [
                        'line'   => $rowIndex,
                        'record' => [
                            'cp_emp_id'            => $user->id,
                            'achievement_cards_id' => $cardId,
                            'receive_date'         => $date,
                            'add_date'             => new DateTimeImmutable(),
                        ],
                    ];
                } catch (Throwable $e) {
                    $errors[] = [
                        'message'   => 'Данные не соответствуют формату файла.',
                        'realError' => $e->getMessage(),
                        'line'      => $rowIndex,
                        'dataset'   => [
                            'employeeName' => $tmp[0],
                            'unlockedAt'   => $tmp[1],
                        ],
                    ];
                }
            }
        }

        $successCount = 0;
        foreach ($dataset as $data) {
            try {
                $this->writeRepository->insert($data['record']);
                $successCount++;
            } catch (Throwable $e) {
                $errors[] =
                    [
                        'message' => $this->mapDatabaseError($e),
                        'line'    => $data['line'],
                        'dataset' => $data['record'],
                    ];
            }
        }

        usort($errors, function (array $a, array $b): int {
            return $a['line'] <=> $b['line'];
        });

        $reader->close();
        unlink($tmpName);

        return [
            'errors'       => $errors,
            'linesInFile'  => $total,
            'successCount' => $successCount,
        ];
    }
}
