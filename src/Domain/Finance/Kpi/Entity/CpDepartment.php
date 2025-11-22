<?php

declare(strict_types=1);

namespace App\Domain\Finance\Kpi\Entity;

use Database\ORM\Attribute\Column;
use Database\ORM\Attribute\Entity;

#[Entity('test.cp_departament')]
class CpDepartment
{
    public function __construct(
        #[Column] private int $id,
        #[Column] private string $name,
        /** @var array<int, Post> */
        #[Column(collectionOf: Post::class)] private array $posts = [],
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function addPost(Post $post): void
    {
        $this->posts[$post->getId()] = $post;
    }

    public function removePost(int $postId): void
    {
        unset($this->posts[$postId]);
    }

    public function getPosts(): array
    {
        return $this->posts;
    }

    public function toArray(): array
    {
        $posts = array_values(array_map(fn ($x): array => $x->toArray(), $this->posts));

        return [
            'id'    => $this->id,
            'name'  => $this->name,
            'posts' => $posts,
        ];
    }
}
