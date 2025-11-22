<?php

declare(strict_types=1);

namespace App\Domain\Marketing\CustomerHistory\Repository;

use App\Domain\Marketing\CustomerHistory\Entity\HistoryItem;
use Database\Connection\ParamMode;
use Database\Connection\ParamType;
use Database\ORM\CommandRepository;

/**
 * @extends CommandRepository<HistoryItem>
 */
class CustomerHistoryCommandRepository extends CommandRepository
{
    protected string $entityName = HistoryItem::class;

    /**
     * @procedure tehno.shop_cursor_stories.edit_story_of_customer
     * @comment Добавляет историю покупателя
     */
    public function editStoryOfCustomer(
        int $id,
        int $userId,
        int $status,
        ?string $preview,
        ?string $text,
        ?string $commentary,
        string $shops,
    ): void {
        $this->conn->procedure('tehno.shop_cursor_stories.edit_story_of_customer', [
            'p_Id'         => $id,
            'p_User_Id'    => $userId,
            'p_Status'     => $status,
            'p_Preview'    => $preview,
            'p_Text'       => $text,
            'p_Commentary' => $commentary,
            'p_Shops'      => $shops,
        ], [
            'p_Id'         => [ParamMode::IN, ParamType::INTEGER],
            'p_User_Id'    => [ParamMode::IN, ParamType::INTEGER],
            'p_Status'     => [ParamMode::IN, ParamType::INTEGER],
            'p_Preview'    => [ParamMode::IN, ParamType::STRING],
            'p_Text'       => [ParamMode::IN, ParamType::STRING],
            'p_Commentary' => [ParamMode::IN, ParamType::STRING],
            'p_Shops'      => [ParamMode::IN, ParamType::STRING],
        ]);
    }
}
