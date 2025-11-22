<?php

declare(strict_types=1);

use App\Domain\Dit\Reporter\Entity\ReportField;

it('creates ReportField with default values', function (): void {
    $field = new ReportField();

    expect($field->fieldName)->toBe('');
    expect($field->bandName)->toBe('');
    expect($field->displayLabel)->toBe('');
    expect($field->isCurrency)->toBe(false);
});

it('creates ReportField with custom values', function (): void {
    $field = new ReportField(
        fieldName: 'total_amount',
        bandName: 'financial_data',
        displayLabel: 'Total Amount',
        isCurrency: true
    );

    expect($field->fieldName)->toBe('total_amount');
    expect($field->bandName)->toBe('financial_data');
    expect($field->displayLabel)->toBe('Total Amount');
    expect($field->isCurrency)->toBe(true);
});

it('creates ReportField with partial values', function (): void {
    $field = new ReportField(
        fieldName: 'user_name',
        displayLabel: 'User Name'
    );

    expect($field->fieldName)->toBe('user_name');
    expect($field->bandName)->toBe('');
    expect($field->displayLabel)->toBe('User Name');
    expect($field->isCurrency)->toBe(false);
});

it('handles currency field correctly', function (): void {
    $currencyField = new ReportField(isCurrency: true);
    $nonCurrencyField = new ReportField(isCurrency: false);
    $defaultField = new ReportField();

    expect($currencyField->isCurrency)->toBe(true);
    expect($nonCurrencyField->isCurrency)->toBe(false);
    expect($defaultField->isCurrency)->toBe(false);
});

it('is readonly class', function (): void {
    $field = new ReportField(fieldName: 'test_field');

    expect($field->fieldName)->toBe('test_field');

    // Attempting to modify should result in an error (readonly property)
    expect(function () use ($field): void {
        $field->fieldName = 'modified_field';
    })->toThrow(Error::class);
});

it('creates field with all properties set', function (): void {
    $field = new ReportField(
        fieldName: 'invoice_total',
        bandName: 'invoice_band',
        displayLabel: 'Invoice Total ($)',
        isCurrency: true
    );

    expect($field->fieldName)->toBe('invoice_total');
    expect($field->bandName)->toBe('invoice_band');
    expect($field->displayLabel)->toBe('Invoice Total ($)');
    expect($field->isCurrency)->toBe(true);
});

it('handles empty string values', function (): void {
    $field = new ReportField(
        fieldName: '',
        bandName: '',
        displayLabel: '',
        isCurrency: false
    );

    expect($field->fieldName)->toBe('');
    expect($field->bandName)->toBe('');
    expect($field->displayLabel)->toBe('');
    expect($field->isCurrency)->toBe(false);
});

it('handles long field names and labels', function (): void {
    $longFieldName = 'very_long_field_name_with_many_characters_in_it';
    $longDisplayLabel = 'Very Long Display Label With Many Words In It For Testing Purposes';
    $longBandName = 'very_long_band_name_for_comprehensive_testing';

    $field = new ReportField(
        fieldName: $longFieldName,
        bandName: $longBandName,
        displayLabel: $longDisplayLabel,
        isCurrency: false
    );

    expect($field->fieldName)->toBe($longFieldName);
    expect($field->bandName)->toBe($longBandName);
    expect($field->displayLabel)->toBe($longDisplayLabel);
    expect($field->isCurrency)->toBe(false);
});

it('creates field for different types of data', function (): void {
    $textField = new ReportField(
        fieldName: 'description',
        displayLabel: 'Description'
    );

    $numberField = new ReportField(
        fieldName: 'quantity',
        displayLabel: 'Quantity'
    );

    $currencyField = new ReportField(
        fieldName: 'price',
        displayLabel: 'Price',
        isCurrency: true
    );

    expect($textField->fieldName)->toBe('description');
    expect($textField->isCurrency)->toBe(false);

    expect($numberField->fieldName)->toBe('quantity');
    expect($numberField->isCurrency)->toBe(false);

    expect($currencyField->fieldName)->toBe('price');
    expect($currencyField->isCurrency)->toBe(true);
});
