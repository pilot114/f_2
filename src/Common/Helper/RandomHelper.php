<?php

declare(strict_types=1);

namespace App\Common\Helper;

use DateTimeImmutable;
use Exception;

class RandomHelper
{
    // Theoretically safe password for non-critical cases such as default password for new user
    public static function generateUserPassword(int $length = 20): string
    {
        if ($length > 64) {
            throw new Exception('Password length must be less than 64 symbols.');
        }
        $dateTime = new DateTimeImmutable();
        $pw = 'Cjhjrnsczxj,tpmzyd;jgeceyekb,fyfy' . $dateTime->format('u');
        $pw .= md5($pw);
        $pw = (string) preg_replace('/([ab])/', '$1A!', $pw);
        return substr($pw, -$length);
    }
}
