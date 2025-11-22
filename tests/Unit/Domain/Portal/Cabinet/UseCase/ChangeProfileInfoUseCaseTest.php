<?php

declare(strict_types=1);

namespace App\Tests\Unit\User\Cabinet\UseCase;

use App\Common\Service\Integration\RpcClient;
use App\Domain\Portal\Cabinet\DTO\WorkTime as WorkTimeDto;
use App\Domain\Portal\Cabinet\Entity\Profile;
use App\Domain\Portal\Cabinet\Entity\WorkTime;
use App\Domain\Portal\Cabinet\Enum\WorkTimeTimeZone;
use App\Domain\Portal\Cabinet\Repository\ProfileCommandRepository;
use App\Domain\Portal\Cabinet\Repository\ProfileQueryRepository;
use App\Domain\Portal\Cabinet\Repository\WorkTimeCommandRepository;
use App\Domain\Portal\Cabinet\UseCase\ChangeProfileInfoUseCase;
use Database\Connection\TransactionInterface;
use DateTimeImmutable;
use Mockery;
use Symfony\Component\HttpFoundation\Request;

beforeEach(function (): void {
    $this->read = Mockery::mock(ProfileQueryRepository::class);
    $this->write = Mockery::mock(ProfileCommandRepository::class);
    $this->writeWorkTime = Mockery::mock(WorkTimeCommandRepository::class);
    $this->rpcClient = Mockery::mock(RpcClient::class);
    $this->transaction = Mockery::mock(TransactionInterface::class);
    $this->request = new Request();

    $this->useCase = new ChangeProfileInfoUseCase(
        $this->write,
        $this->read,
        $this->writeWorkTime,
        $this->rpcClient,
        $this->transaction
    );
});

afterEach(function (): void {
    Mockery::close();
});

it('change profile info', function (Profile $profile): void {

    $telegram = 'newtelegram';
    $phone = '7989858554122';
    $city = 'Москва';

    $workTimeDto = new WorkTimeDto(
        new DateTimeImmutable('2025-01-01 08:00'),
        new DateTimeImmutable('2025-01-01 18:00'),
        WorkTimeTimeZone::MOSCOW
    );

    $workTime = new WorkTime(
        1,
        9999,
        $workTimeDto->start,
        $workTimeDto->end,
        $workTimeDto->timeZone
    );

    $this->read->shouldReceive('getProfileByUserId')
        ->once()
        ->with($profile->getUserId())
        ->andReturn($profile);

    $this->transaction->shouldReceive('beginTransaction')->once();

    $this->write->shouldReceive('updateInfo')
        ->once()
        ->withArgs(fn (Profile $profile): bool => $profile->getTelegram() === $telegram
            && $profile->getCity() === $city
            && $profile->getPhone() === $phone
        );

    $this->writeWorkTime->shouldReceive('updateWorkTime')
        ->once();

    $this->transaction->shouldReceive('commit')->once();

    $this->rpcClient->shouldReceive('call')->once();

    $this->useCase->changeProfileInfo($profile->getUserId(), true, $telegram, $phone, $city, $workTimeDto);

    expect($profile->getWorkTime()->toArray())
        ->toBe($workTime->toArray());
    expect($profile->getHideBirthday())->toBeTrue();
})->with('profile');

it('change profile info with empty work time', function (Profile $profile): void {

    $telegram = 'newtelegram';
    $phone = '7989858554122';
    $city = 'Москва';

    $workTimeDto = new WorkTimeDto(
        new DateTimeImmutable('2025-01-01 08:00'),
        new DateTimeImmutable('2025-01-01 18:00'),
        WorkTimeTimeZone::MOSCOW
    );

    $workTime = new WorkTime(
        1,
        9999,
        $workTimeDto->start,
        $workTimeDto->end,
        $workTimeDto->timeZone
    );

    $profile->setWorkTime(null);

    $this->read->shouldReceive('getProfileByUserId')
        ->once()
        ->with($profile->getUserId())
        ->andReturn($profile);

    $this->transaction->shouldReceive('beginTransaction')->once();

    $this->write->shouldReceive('updateInfo')
        ->once()
        ->withArgs(fn (Profile $profile): bool => $profile->getTelegram() === $telegram
            && $profile->getCity() === $city
            && $profile->getPhone() === $phone
        );

    $this->writeWorkTime->shouldReceive('create')
        ->once();

    $this->transaction->shouldReceive('commit')->once();

    $this->rpcClient->shouldReceive('call')->once();

    $this->useCase->changeProfileInfo($profile->getUserId(), null, $telegram, $phone, $city, $workTimeDto);

    expect($profile->getWorkTime()->toArray())
        ->toBe($workTime->toArray());
})->with('profile');
