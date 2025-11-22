<?php

declare(strict_types=1);

namespace App\Domain\Dit\Reporter\Entity;

use App\Domain\Dit\Reporter\XMLParser;

class Report
{
    /**
     * @param ?array{id: int, name: string, email: string} $owner
     */
    public function __construct(
        public readonly int $id,
        protected string    $name,
        protected int       $currentUserInUk,
        protected ?string   $data = null,
        protected ?array    $owner = null,
    ) {
    }

    public function toArray(): array
    {
        $data = $this->getData();

        if ($data !== []) {
            // учитываем только основной запрос
            $data['query'] = $data['queries'][0]->toArray();
        }

        return [
            'id'    => $this->id,
            'name'  => $this->name,
            'owner' => $this->owner,
            'data'  => $data,
        ];
    }

    /**
     * @return ?array{id: int, name: string, email: string}
     */
    public function getOwner(): ?array
    {
        return $this->owner;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getData(): array
    {
        if ($this->data === null) {
            return [];
        }
        return XMLParser::parse($this->data);
    }

    public function getParams(): array
    {
        $params = [];
        $exceptions = [];

        $data = $this->getData();

        while ($report = array_pop($data)) {
            foreach ($report['params'] ?? [] as $param) {
                if (!in_array($param['name'], $exceptions, true)) {
                    $params[] = $param;
                    $exceptions[] = $param['name'];
                }
            }
            if (!empty($report['sub'])) {
                $data = array_merge($data, $report['sub']);
            }
        }

        return $params;
    }
}
