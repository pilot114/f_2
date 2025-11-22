<?php

declare(strict_types=1);

namespace App\Domain\Analytics\Mcp\Enum;

use App\Common\DTO\Titleable;
use Database\Schema\DbObject\DbObjectType;
use LogicException;

enum ArtefactType: string implements Titleable
{
    // database artefact types
    case TABLE = 'TABLE';
    case VIEW = 'VIEW';
    case PROCEDURE = 'PROCEDURE';
    case TRIGGER = 'TRIGGER';
    case FUNCTION = 'FUNCTION';
    case PACKAGE = 'PACKAGE';
    case SCHEMA = 'SCHEMA';

    // TODO: jira
    // TODO: github

    public function getTitle(): string
    {
        return match ($this) {
            self::TABLE     => 'Таблица',
            self::VIEW      => 'Представление',
            self::PROCEDURE => 'Процедура',
            self::TRIGGER   => 'Триггер',
            self::FUNCTION  => 'Функция',
            self::PACKAGE   => 'Пакет',
            self::SCHEMA    => 'Схема',
        };
    }

    public static function fromDbObjectType(DbObjectType $dbType): self
    {
        return match ($dbType) {
            DbObjectType::Table     => self::TABLE,
            DbObjectType::Procedure => self::PROCEDURE,
            DbObjectType::View      => self::VIEW,
            default                 => throw new LogicException('Неподдерживаемый тип ' . $dbType::class),
        };
    }

    public function toDbObjectType(): DbObjectType
    {
        return match ($this) {
            self::TABLE     => DbObjectType::Table,
            self::PROCEDURE => DbObjectType::Procedure,
            self::VIEW      => DbObjectType::View,
            default         => throw new LogicException('Неподдерживаемый тип ' . $this::class),
        };
    }
}
