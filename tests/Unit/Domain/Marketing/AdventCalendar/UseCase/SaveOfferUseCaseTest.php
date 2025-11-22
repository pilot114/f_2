<?php

declare(strict_types=1);

namespace App\Tests\Unit\Marketing\AdventCalendar\UseCase;

use App\Domain\Marketing\AdventCalendar\DTO\SaveOfferLanguageRequest;
use App\Domain\Marketing\AdventCalendar\DTO\SaveOfferRequest;
use App\Domain\Marketing\AdventCalendar\Entity\OfferLanguage;
use App\Domain\Marketing\AdventCalendar\Repository\GetOfferQueryRepository;
use App\Domain\Marketing\AdventCalendar\Repository\WriteOfferCommandRepository;
use App\Domain\Marketing\AdventCalendar\UseCase\SaveOfferUseCase;
use Database\Connection\TransactionInterface;
use DomainException;

beforeEach(function (): void {
    $this->repositoryWrite = $this->createMock(WriteOfferCommandRepository::class);
    $this->repositoryGet = $this->createMock(GetOfferQueryRepository::class);
    $this->transaction = $this->createMock(TransactionInterface::class);
    $this->useCase = new SaveOfferUseCase($this->repositoryWrite, $this->repositoryGet, $this->transaction);
});

test('saveOffer creates new offer successfully', function (): void {
    // Arrange
    $langs = [
        new SaveOfferLanguageRequest(
            lang: 'en',
            shortTitle: 'Sale',
            typeName: 'discount',
            shortDescr: 'Big sale',
            fullDescription: 'Full description',
            buttonText: 'Get Offer',
            newsLink: 'news.html',
            imageId: 700,
        ),
    ];

    $request = new SaveOfferRequest(
        calendarId: 123,
        active: 1,
        bkImageId: 456,
        offerId: null,
        langs: $langs
    );

    $this->transaction->expects($this->once())->method('beginTransaction');
    $this->repositoryWrite
        ->expects($this->once())
        ->method('addOffer')
        ->with(123, 'discount', true, 456)
        ->willReturn([
            'p_Out' => 789,
        ]);
    $this->repositoryWrite
        ->expects($this->once())
        ->method('updateOfferLang')
        ->with(789, 'en', 'discount', 'Get Offer', 'Sale', 'Big sale', 'Sale', 'Full description', null, 'news.html');
    $this->transaction->expects($this->once())->method('commit');

    // Act
    $result = $this->useCase->saveOffer($request);

    // Assert
    expect($result)->toBe(789);
});

test('saveOffer throws exception when offer creation fails', function (): void {
    // Arrange
    $request = new SaveOfferRequest(
        calendarId: 123,
        active: 1,
        bkImageId: 456,
        offerId: null,
        langs: [new OfferLanguage(lang: 'en', shortTitle: 'short', typeName: 'test')]
    );

    $this->transaction->expects($this->once())->method('beginTransaction');
    $this->repositoryWrite
        ->method('addOffer')
        ->willReturn([
            'p_Out' => 0,
        ]);

    $this->repositoryGet
        ->method('getOfferImage')
        ->willReturn('https://example.com/image.jpg');

    // Act & Assert
    expect(fn () => $this->useCase->saveOffer($request))
        ->toThrow(DomainException::class, 'Не удалось создать предложение');
});

test('saveOffer updates existing offer successfully', function (): void {
    // Arrange
    $langs = [
        new SaveOfferLanguageRequest(
            lang: 'en',
            shortTitle: 'Updated Sale',
            typeName: 'discount',
            shortDescr: 'Updated description',
            fullDescription: 'Updated full description',
            buttonText: 'Updated Button',
            newsLink: 'updated-news.html',
            imageId: 700
        ),
        new SaveOfferLanguageRequest(
            lang: 'ru',
            shortTitle: 'Распродажа',
            typeName: 'скидка',
            shortDescr: 'Описание',
            fullDescription: 'Полное описание',
            buttonText: 'Кнопка',
            newsLink: 'news-ru.html',
            imageId: 700
        ),
    ];

    $request = new SaveOfferRequest(
        calendarId: 123,
        active: 1,
        bkImageId: 456,
        offerId: 999,
        langs: $langs
    );

    $this->transaction->expects($this->once())->method('beginTransaction');
    $this->repositoryWrite
        ->expects($this->once())
        ->method('updateOffer')
        ->with(999, 123, 'discount', true, 456);
    $this->repositoryWrite
        ->expects($this->exactly(2))
        ->method('updateOfferLang');
    $this->transaction->expects($this->once())->method('commit');

    // Act
    $result = $this->useCase->saveOffer($request);

    // Assert
    expect($result)->toBe(999);
});

test('saveOffer throws exception when offerId is negative', function (): void {
    // Arrange
    $request = new SaveOfferRequest(
        calendarId: 123,
        active: 1,
        bkImageId: 456,
        offerId: null,
        langs: [new SaveOfferLanguageRequest(lang: 'en', shortTitle: 'short', typeName: 'test')]
    );

    $this->transaction->expects($this->once())->method('beginTransaction');
    $this->repositoryWrite
        ->method('addOffer')
        ->willReturn([
            'p_Out' => -1,
        ]);

    // Act & Assert
    expect(fn () => $this->useCase->saveOffer($request))
        ->toThrow(DomainException::class, 'Не удалось создать предложение');
});

test('removeOffer marks offer as deleted', function (): void {
    // Arrange
    $offerId = 123;
    $calendarId = 456;

    $this->repositoryWrite
        ->expects($this->once())
        ->method('updateOffer')
        ->with($offerId, $calendarId, 'deleted', 0, 1);

    // Act
    $result = $this->useCase->removeOffer($offerId, $calendarId);

    // Assert
    expect($result)->toBeTrue();
});
