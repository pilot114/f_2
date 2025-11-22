<?php

declare(strict_types=1);

namespace App\Domain\Dit\Reporter;

use App\Domain\Dit\Reporter\Entity\ReportQuery;
use SimpleXMLElement;

class XMLParser
{
    public static function parse(string $xml): array
    {
        $xml = (string) mb_convert_encoding($xml, 'UTF-8', 'CP1251');
        $xml = (string) preg_replace_callback(
            '/(?<=>)"(.*?)"(?=<)/',
            static fn ($matches): string => htmlspecialchars($matches[1]),
            $xml
        );
        $xmlObject = new SimpleXMLElement($xml);

        return [
            'databaseName' => (string) $xmlObject->DATABASENAME,
            'queries'      => self::parseQueries($xmlObject->QUERIES),
        ];
    }

    /**
     * @return ReportQuery[]
     */
    private static function parseQueries(SimpleXMLElement $queries): array
    {
        $data = [];
        foreach ($queries->QueryDescription as $query) {
            $tmp = [
                'sql'     => self::getLines($query->SQL->LINE),
                'caption' => (string) $query->CAPTION,

                'keyField'    => (string) $query->KEYFIELDS,
                'masterField' => (string) $query->MASTERFIELDS,
                'detailField' => (string) $query->DETAILFIELDS,

                'fields' => self::parseFields($query->FIELDDESCRIPTIONS->FieldDescription),
                'params' => self::parseParams($query->PARAMS->ParamDescription),
                'sub'    => isset($query->ITEMS->QueryDescription) ? self::parseQueries($query->ITEMS) : [],
            ];
            $data[] = new ReportQuery(...$tmp);
        }
        return $data;
    }

    private static function getLines(SimpleXMLElement $lines): string
    {
        $sql = '';
        foreach ($lines as $line) {
            $sql .= $line . PHP_EOL;
        }
        return trim($sql);
    }

    private static function parseFields(SimpleXMLElement $elements): array
    {
        $parsed = [];
        foreach ($elements as $element) {
            $parsed[] = [
                'bandName'     => (string) $element->BANDNAME,
                'fieldName'    => mb_strtolower((string) $element->FIELDNAME),
                'displayLabel' => (string) $element->DISPLAYLABEL,
                'isCurrency'   => self::convertBoolean((string) $element->ISCURRENCY),
            ];
        }
        return $parsed;
    }

    private static function parseParams(SimpleXMLElement $elements): array
    {
        $parsed = [];
        foreach ($elements as $element) {
            $parsed[] = [
                'name'         => (string) $element->NAME,
                'caption'      => (string) $element->CAPTION,
                'dataType'     => (string) $element->DATATYPE,
                'defaultValue' => (string) $element->DEFAULTVALUE,
                'dictionaryId' => (int) $element->DICTIONARYID,
                'customValues' => (string) $element->CUSTOMVALUES,
                'required'     => self::convertBoolean((string) $element->REQUIRED),
            ];
        }
        return $parsed;
    }

    private static function convertBoolean(string $value): bool
    {
        if ($value === 'True') {
            return true;
        }
        if ($value === 'False') {
            return false;
        }
        return (bool) $value;
    }
}
