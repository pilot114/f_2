<?php

declare(strict_types=1);

namespace App\Common\DTO;

/**
 *  В данный момент нигде не используется
 * @see \App\Common\Service\Integration\ProductImageClient
 */
enum ProductImageSize: string
{
    case NOSIZE = '';
    case SIZE60 = 'small';
    case SIZE150 = 'w150';
    case SIZE220 = 'medium_small';
    case SIZE300 = 'medium';

    public function isNoSize(): bool
    {
        return $this === self::NOSIZE;
    }
}
