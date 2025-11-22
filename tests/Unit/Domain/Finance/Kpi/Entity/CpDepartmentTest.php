<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Finance\Kpi\Entity;

use App\Domain\Finance\Kpi\Entity\CpDepartment;
use App\Domain\Finance\Kpi\Entity\Post;

it('creates cp department with id and name', function (): void {
    $department = new CpDepartment(id: 1, name: 'IT Department');

    expect($department->getId())->toBe(1)
        ->and($department->getName())->toBe('IT Department');
});

it('returns correct name', function (): void {
    $department = new CpDepartment(id: 1, name: 'Sales Department');

    expect($department->getName())->toBe('Sales Department');
});

it('returns correct id', function (): void {
    $department = new CpDepartment(id: 42, name: 'HR Department');

    expect($department->getId())->toBe(42);
});

it('adds post to department', function (): void {
    $department = new CpDepartment(id: 1, name: 'Department');
    $post = new Post(id: 1, name: 'Manager');

    $department->addPost($post);

    expect($department->getPosts())->toHaveCount(1)
        ->and($department->getPosts()[1])->toBe($post);
});

it('adds multiple posts', function (): void {
    $department = new CpDepartment(id: 1, name: 'Department');
    $post1 = new Post(id: 1, name: 'Manager');
    $post2 = new Post(id: 2, name: 'Developer');

    $department->addPost($post1);
    $department->addPost($post2);

    expect($department->getPosts())->toHaveCount(2);
});

it('removes post from department', function (): void {
    $department = new CpDepartment(id: 1, name: 'Department');
    $post = new Post(id: 1, name: 'Manager');

    $department->addPost($post);
    $department->removePost(1);

    expect($department->getPosts())->toBeEmpty();
});

it('removes specific post by id', function (): void {
    $department = new CpDepartment(id: 1, name: 'Department');
    $post1 = new Post(id: 1, name: 'Manager');
    $post2 = new Post(id: 2, name: 'Developer');
    $post3 = new Post(id: 3, name: 'Designer');

    $department->addPost($post1);
    $department->addPost($post2);
    $department->addPost($post3);

    $department->removePost(2);

    expect($department->getPosts())->toHaveCount(2)
        ->and($department->getPosts())->toHaveKey(1)
        ->and($department->getPosts())->toHaveKey(3)
        ->and($department->getPosts())->not->toHaveKey(2);
});

it('converts to array without posts', function (): void {
    $department = new CpDepartment(id: 10, name: 'Marketing');

    $result = $department->toArray();

    expect($result)->toBe([
        'id'    => 10,
        'name'  => 'Marketing',
        'posts' => [],
    ]);
});

it('converts to array with posts', function (): void {
    $department = new CpDepartment(id: 1, name: 'Department');
    $post1 = new Post(id: 1, name: 'Manager');
    $post2 = new Post(id: 2, name: 'Developer');

    $department->addPost($post1);
    $department->addPost($post2);

    $result = $department->toArray();

    expect($result['id'])->toBe(1)
        ->and($result['name'])->toBe('Department')
        ->and($result['posts'])->toHaveCount(2)
        ->and($result['posts'][0])->toHaveKey('id')
        ->and($result['posts'][0])->toHaveKey('name');
});

it('handles cyrillic department name', function (): void {
    $department = new CpDepartment(id: 1, name: 'Отдел разработки');

    expect($department->getName())->toBe('Отдел разработки')
        ->and($department->toArray()['name'])->toBe('Отдел разработки');
});

it('getPosts returns empty array initially', function (): void {
    $department = new CpDepartment(id: 1, name: 'New Department');

    expect($department->getPosts())->toBeEmpty();
});

it('replaces post with same id', function (): void {
    $department = new CpDepartment(id: 1, name: 'Department');
    $post1 = new Post(id: 1, name: 'Manager');
    $post2 = new Post(id: 1, name: 'Senior Manager');

    $department->addPost($post1);
    $department->addPost($post2);

    expect($department->getPosts())->toHaveCount(1)
        ->and($department->getPosts()[1])->toBe($post2);
});
