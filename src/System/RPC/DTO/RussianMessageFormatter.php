<?php

declare(strict_types=1);

namespace App\System\RPC\DTO;

use CuyZ\Valinor\Mapper\Tree\Message\Formatter\MessageFormatter;
use CuyZ\Valinor\Mapper\Tree\Message\NodeMessage;

class RussianMessageFormatter implements MessageFormatter
{
    public const TRANSLATIONS = [
        'Value {source_value} does not match any of {allowed_values}.' => [
            'ru' => 'Значение {source_value} не соответствует ни одному из {allowed_values}.',
        ],
        'Value {source_value} does not match any of {allowed_types}.' => [
            'ru' => 'Значение {source_value} не соответствует ни одному из {allowed_types}.',
        ],
        'Cannot be empty and must be filled with a value matching any of {allowed_types}.' => [
            'ru' => 'Не может быть пустым и должно быть заполнено значением, соответствующим любому из {allowed_types}.',
        ],
        'Value {source_value} does not match type {expected_type}.' => [
            'ru' => 'Значение {source_value} не соответствует типу {expected_type}.',
        ],
        'Value {source_value} does not match {expected_value}.' => [
            'ru' => 'Значение {source_value} не соответствует {expected_value}.',
        ],
        'Value {source_value} does not match boolean value {expected_value}.' => [
            'ru' => 'Значение {source_value} не соответствует булевому значению {expected_value}.',
        ],
        'Value {source_value} does not match float value {expected_value}.' => [
            'ru' => 'Значение {source_value} не соответствует значению с плавающей точкой {expected_value}.',
        ],
        'Value {source_value} does not match integer value {expected_value}.' => [
            'ru' => 'Значение {source_value} не соответствует целочисленному значению {expected_value}.',
        ],
        'Value {source_value} does not match string value {expected_value}.' => [
            'ru' => 'Значение {source_value} не соответствует строковому значению {expected_value}.',
        ],
        'Value {source_value} is not null.' => [
            'ru' => 'Значение {source_value} не является null.',
        ],
        'Value {source_value} is not a valid boolean.' => [
            'ru' => 'Значение {source_value} не является допустимым булевым значением.',
        ],
        'Value {source_value} is not a valid float.' => [
            'ru' => 'Значение {source_value} не является допустимым числом с плавающей точкой.',
        ],
        'Value {source_value} is not a valid integer.' => [
            'ru' => 'Значение {source_value} не является допустимым целым числом.',
        ],
        'Value {source_value} is not a valid string.' => [
            'ru' => 'Значение {source_value} не является допустимой строкой.',
        ],
        'Value {source_value} is not a valid negative integer.' => [
            'ru' => 'Значение {source_value} не является допустимым отрицательным целым числом.',
        ],
        'Value {source_value} is not a valid positive integer.' => [
            'ru' => 'Значение {source_value} не является допустимым положительным целым числом.',
        ],
        'Value {source_value} is not a valid non-empty string.' => [
            'ru' => 'Значение {source_value} не является допустимой непустой строкой.',
        ],
        'Value {source_value} is not a valid numeric string.' => [
            'ru' => 'Значение {source_value} не является допустимой числовой строкой.',
        ],
        'Value {source_value} is not a valid integer between {min} and {max}.' => [
            'ru' => 'Значение {source_value} не является допустимым целым числом между {min} и {max}.',
        ],
        'Value {source_value} is not a valid timezone.' => [
            'ru' => 'Значение {source_value} не является допустимым часовым поясом.',
        ],
        'Value {source_value} is not a valid class string.' => [
            'ru' => 'Значение {source_value} не является допустимой строкой класса.',
        ],
        'Value {source_value} is not a valid class string of `{expected_class_type}`.' => [
            'ru' => 'Значение {source_value} не является допустимой строкой класса `{expected_class_type}`.',
        ],
        'Invalid value {source_value}.' => [
            'ru' => 'Недопустимое значение {source_value}.',
        ],
        'Invalid value {source_value}, it matches at least two types from union.' => [
            'ru' => 'Недопустимое значение {source_value}, оно соответствует по крайней мере двум типам из объединения.',
        ],
        'Invalid value {source_value}, it matches at least two types from {allowed_types}.' => [
            'ru' => 'Недопустимое значение {source_value}, оно соответствует по крайней мере двум типам из {allowed_types}.',
        ],
        'Invalid sequential key {key}, expected {expected}.' => [
            'ru' => 'Недопустимый последовательный ключ {key}, ожидается {expected}.',
        ],
        'Cannot be empty.' => [
            'ru' => 'Не может быть пустым.',
        ],
        'Cannot be empty and must be filled with a value matching type {expected_type}.' => [
            'ru' => 'Не может быть пустым и должно быть заполнено значением типа {expected_type}.',
        ],
        'Key {key} does not match type {expected_type}.' => [
            'ru' => 'Ключ {key} не соответствует типу {expected_type}.',
        ],
        'Value {source_value} does not match a valid date format.' => [
            'ru' => 'Значение {source_value} не соответствует допустимому формату даты.',
        ],
        'Value {source_value} does not match any of the following formats: {formats}.' => [
            'ru' => 'Значение {source_value} не соответствует ни одному из следующих форматов: {formats}.',
        ],
        'Invalid value {source_value}, it matches two or more types from union: cannot take a decision.' => [
            'ru' => 'Недопустимое значение {source_value}, оно соответствует двум или более типам за объединения: невозможно принять решение',
        ],
    ];

    public function format(NodeMessage $message): NodeMessage
    {
        $body = self::TRANSLATIONS[$message->body()]['ru'] ?? null;

        if ($body) {
            return $message->withBody($body);
        }

        return $message;
    }
}
