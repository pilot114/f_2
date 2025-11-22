<?php

declare(strict_types=1);

namespace App\Domain\Marketing\CustomerHistory\DTO;

use App\Domain\Marketing\CustomerHistory\Enum\Status;
use Symfony\Component\Validator\Constraints as Assert;

readonly class EditCustomerHistoryRequest
{
    public function __construct(
        #[Assert\NotBlank(message: 'ID истории клиента обязателен')]
        #[Assert\Positive(message: 'ID должен быть положительным числом')]
        public int $id,

        public Status $status,
        #[Assert\NotBlank(message: 'Список стран публикации обязателен')]
        #[Assert\Type(type: 'array', message: 'Список стран публикации должен быть массивом')]
        #[Assert\Count(
            min: 1,
            minMessage: 'Должен быть выбран хотя бы один магазин'
        )]
        #[Assert\All([
            new Assert\NotBlank(message: 'Код магазина не может быть пустым'),
            new Assert\Type(type: 'string', message: 'Код магазина должен быть строкой'),
            new Assert\Regex(
                pattern: '/^[a-z]{2}$/',
                message: 'Код магазина должен состоять из 2 строчных латинских букв'
            ),
        ])]
        /** @var array<string> */
        public array $shops,

        #[Assert\NotBlank(message: 'Превью истории обязательно')]
        #[Assert\Length(
            max: 250,
            maxMessage: 'Превью не может содержать более {{ limit }} символов'
        )]
        public string $preview = '',

        #[Assert\NotBlank(message: 'Текст истории обязателен')]
        public string $text = '',

        #[Assert\Length(
            max: 250,
            maxMessage: 'Комментарий не может содержать более {{ limit }} символов'
        )]
        public ?string $commentary = '',
    ) {
    }

    /**
     * Получить строку магазинов для процедуры (склеенную через запятую)
     */
    public function getShopsString(): string
    {
        return implode(',', $this->shops);
    }
}
