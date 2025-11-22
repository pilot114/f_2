<?php

declare(strict_types=1);

namespace App\Tests\Unit\User\Cabinet\Repository;

use App\Domain\Portal\Cabinet\Entity\Congratulation;
use App\Domain\Portal\Cabinet\Repository\CongratulationsQueryRepository;
use Closure;
use Database\Connection\ReadDatabaseInterface;
use DateTimeImmutable;
use Generator;
use Illuminate\Support\Collection;
use Mockery;

it('find by user Id', function (): void {
    $connection = Mockery::mock(ReadDatabaseInterface::class);
    $repository = new CongratulationsQueryRepository($connection, getDataMapper());
    $currentUserId = 9999;

    $congratulation = new Congratulation(
        id: $id = 123,
        fromUserId: $fromUserId = 9999,
        fromUserName: $fromUserName = 'Иванов Иван',
        message: $message = 'поздравляю',
        year: $year = new DateTimeImmutable('2024-01-01'),
        avatar: $avatar = '/image.jpg'
    );
    $collection = new Collection();
    $collection->put($id, $congratulation);

    $data = [
        [
            'id'             => (string) $id,
            'from_user_id'   => (string) $fromUserId,
            'from_user_name' => $fromUserName,
            'message'        => $message,
            'year'           => $year->format('Y-m-d'),
            'fpath'          => $avatar,
        ],
    ];

    $generator = function (array $data): Closure {
        return function () use ($data): Generator {
            foreach ($data as $item) {
                yield $item;
            }
        };
    };

    $connection->shouldReceive('query')
        ->once()
        ->withArgs(function ($sql, array $params) use ($currentUserId, $year): bool {
            return $params['receiverId'] === $currentUserId
                && $params['year'] === (int) $year->format('Y');
        })
        ->andReturnUsing($generator($data));

    $result = $repository->findCongratulationsByReceiverId($currentUserId, $year);

    expect(serialize($result->first()))->toBe(serialize($collection->first()));
});
