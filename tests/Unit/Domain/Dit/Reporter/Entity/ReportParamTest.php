<?php

declare(strict_types=1);

use App\Domain\Dit\Reporter\Entity\ReportParam;

it('creates ReportParam with default values', function (): void {
    $param = new ReportParam();

    expect($param->name)->toBe('');
    expect($param->caption)->toBe('');
    expect($param->dataType)->toBe('');
    expect($param->defaultValue)->toBe('');
    expect($param->dictionaryId)->toBe(0);
    expect($param->customValues)->toBe('');
    expect($param->required)->toBe(false);
});

it('creates ReportParam with custom values', function (): void {
    $param = new ReportParam(
        name: 'user_id',
        caption: 'User ID',
        dataType: 'ftInteger',
        defaultValue: '1',
        dictionaryId: 100,
        customValues: 'custom1,custom2',
        required: true
    );

    expect($param->name)->toBe('user_id');
    expect($param->caption)->toBe('User ID');
    expect($param->dataType)->toBe('ftInteger');
    expect($param->defaultValue)->toBe('1');
    expect($param->dictionaryId)->toBe(100);
    expect($param->customValues)->toBe('custom1,custom2');
    expect($param->required)->toBe(true);
});

it('creates ReportParam with partial values', function (): void {
    $param = new ReportParam(
        name: 'date_param',
        caption: 'Date Parameter',
        dataType: 'ftDate',
        required: true
    );

    expect($param->name)->toBe('date_param');
    expect($param->caption)->toBe('Date Parameter');
    expect($param->dataType)->toBe('ftDate');
    expect($param->defaultValue)->toBe('');
    expect($param->dictionaryId)->toBe(0);
    expect($param->customValues)->toBe('');
    expect($param->required)->toBe(true);
});

it('handles different data types', function (): void {
    $stringParam = new ReportParam(dataType: 'ftString');
    $intParam = new ReportParam(dataType: 'ftInteger');
    $dateParam = new ReportParam(dataType: 'ftDate');
    $cursorParam = new ReportParam(dataType: 'ftCursor');
    $arrayParam = new ReportParam(dataType: 'ftArray');

    expect($stringParam->dataType)->toBe('ftString');
    expect($intParam->dataType)->toBe('ftInteger');
    expect($dateParam->dataType)->toBe('ftDate');
    expect($cursorParam->dataType)->toBe('ftCursor');
    expect($arrayParam->dataType)->toBe('ftArray');
});

it('handles required parameter correctly', function (): void {
    $requiredParam = new ReportParam(required: true);
    $optionalParam = new ReportParam(required: false);
    $defaultParam = new ReportParam();

    expect($requiredParam->required)->toBe(true);
    expect($optionalParam->required)->toBe(false);
    expect($defaultParam->required)->toBe(false);
});

it('handles dictionary id correctly', function (): void {
    $paramWithDict = new ReportParam(dictionaryId: 999);
    $paramWithoutDict = new ReportParam();

    expect($paramWithDict->dictionaryId)->toBe(999);
    expect($paramWithoutDict->dictionaryId)->toBe(0);
});

it('handles custom values correctly', function (): void {
    $paramWithCustom = new ReportParam(customValues: 'value1,value2,value3');
    $paramEmpty = new ReportParam();

    expect($paramWithCustom->customValues)->toBe('value1,value2,value3');
    expect($paramEmpty->customValues)->toBe('');
});

it('creates complex ReportParam configuration', function (): void {
    $param = new ReportParam(
        name: 'complex_param',
        caption: 'Complex Parameter with Long Name',
        dataType: 'ftString',
        defaultValue: 'default_complex_value',
        dictionaryId: 12345,
        customValues: 'opt1,opt2,opt3,opt4',
        required: true
    );

    expect($param->name)->toBe('complex_param');
    expect($param->caption)->toBe('Complex Parameter with Long Name');
    expect($param->dataType)->toBe('ftString');
    expect($param->defaultValue)->toBe('default_complex_value');
    expect($param->dictionaryId)->toBe(12345);
    expect($param->customValues)->toBe('opt1,opt2,opt3,opt4');
    expect($param->required)->toBe(true);
});
