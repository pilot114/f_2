<?php

declare(strict_types=1);

namespace App\Common\DTO;

use DateTimeInterface;
use Illuminate\Support\Enumerable;

/**
 * DTO для возврата коллекции
 *
 * @template T
 */
class FindResponse
{
    /**
     * @param array<int, T> | Enumerable<int, T> $items
     */
    public function __construct(
        public array|Enumerable $items,
        public int $total = 0,
    ) {
        if ($this->total === 0) {
            $this->total = $this->items instanceof Enumerable
                ? $this->items->getTotal()
                : count($this->items);
        }

        if ($this->items instanceof Enumerable) {
            $this->items = $this->items
                // чтобы эта логика была не только в FindResponse,
                // можно реализовать в \App\System\RPC\RpcServer::normalizeResult
                // НО пока что лучший вариант (для спеки) - делать toArray в DTO
//                ->map(fn($x) => method_exists($x, 'toArray') ? $x->toArray() : $x)
                ->values()->toArray();
        } else {
            $this->items = array_values($this->items);
        }
    }

    /**
     * @return self<T>
     */
    public function recursive(): self
    {
        $processedItems = [];

        foreach ($this->items as $item) {
            $processedItems[] = is_object($item) ? $this->reindexObjectArrays($item) : $item;
        }

        $this->items = $processedItems;
        return $this;
    }

    private function reindexObjectArrays(mixed $o): mixed
    {
        foreach (get_object_vars((object) $o) as $k => $v) {
            if (is_array($v) && $v !== []) {
                $allObjects = array_reduce(
                    $v,
                    fn (bool $carry, mixed $item): bool => $carry && is_object($item),
                    true
                );

                if ($allObjects) {
                    $o->$k = array_values($v);
                }

                // Рекурсивно обрабатываем элементы массива
                foreach ($o->$k as &$item) {
                    if (is_object($item) && !($item instanceof DateTimeInterface)) {
                        $item = $this->reindexObjectArrays($item);
                    }
                }
            } elseif (is_object($v) && !($v instanceof DateTimeInterface)) {

                $objectVars = get_object_vars($v);

                if ($objectVars !== []) {
                    $allValuesAreObjects = array_reduce(
                        $objectVars,
                        fn (bool $carry, mixed $item): bool => $carry && is_object($item) && !($item instanceof DateTimeInterface),
                        true
                    );

                    if ($allValuesAreObjects) {
                        $arrayValues = array_values($objectVars);

                        foreach ($arrayValues as &$item) {
                            $item = $this->reindexObjectArrays($item);
                        }

                        $o->$k = $arrayValues;
                    } else {
                        // Обычная рекурсивная обработка объекта
                        $o->$k = $this->reindexObjectArrays($v);
                    }
                }
            }
        }

        return $o;
    }

}
