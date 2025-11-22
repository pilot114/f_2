<?php

declare(strict_types=1);

dataset('customer history data', [
    [
        // Test case 1: Basic search with all parameters
        [
            'q'        => 'Иванов',
            'state'    => 1,
            'id'       => 'ru',
            'dateFrom' => '2023-01-01',
            'dateTill' => '2023-12-31',
        ],
        [
            [
                'id'                    => '1',
                'create_dt'             => '2023-06-15 10:30:00',
                'employee_name'         => 'Иванов Иван Иванович',
                'employee_contract'     => 'RU123456',
                'history_preview'       => 'Отличный клиент',
                'history'               => 'Полная история клиента...',
                'commentary'            => 'Комментарий модератора',
                'state_id'              => '1',
                'write_country_name'    => 'Россия',
                'write_city_name'       => 'Москва',
                'lang_name'             => 'Русский',
                'state_name'            => 'На модерации',
                'publised_country_id'   => '1',
                'publised_country_name' => 'Россия',
            ],
            [
                'id'                    => '2',
                'create_dt'             => '2023-07-20 14:15:00',
                'employee_name'         => 'Иванова Мария Петровна',
                'employee_contract'     => 'RU789012',
                'history_preview'       => 'Хороший партнер',
                'history'               => 'Детальная история партнера...',
                'commentary'            => null,
                'state_id'              => '2',
                'write_country_name'    => 'Россия',
                'write_city_name'       => 'Санкт-Петербург',
                'lang_name'             => 'Русский',
                'state_name'            => 'Опубликовано',
                'publised_country_id'   => '1',
                'publised_country_name' => 'Россия',
            ],
        ],
    ],
    [
        // Test case 2: Search without parameters
        [
            'q'        => null,
            'state'    => null,
            'lang'     => null,
            'dateFrom' => null,
            'dateTill' => null,
        ],
        [
            [
                'id'                    => '3',
                'create_dt'             => '2023-08-10 09:45:00',
                'employee_name'         => 'Smith John',
                'employee_contract'     => 'US345678',
                'history_preview'       => 'Great customer',
                'history'               => 'Full customer story...',
                'commentary'            => 'Approved by moderator',
                'state_id'              => '2',
                'write_country_name'    => 'США',
                'write_city_name'       => 'New York',
                'lang_name'             => 'English',
                'state_name'            => 'Опубликовано',
                'publised_country_id'   => '2',
                'publised_country_name' => 'США',
            ],
        ],
    ],
]);

dataset('product countries data', [
    [
        'ru',
        [
            [
                'id'      => 'RU',
                'name_ru' => 'Россия',
            ],
            [
                'id'      => 'BY',
                'name_ru' => 'Беларусь',
            ],
            [
                'id'      => 'KZ',
                'name_ru' => 'Казахстан',
            ],
        ],
    ],
    [
        'en',
        [
            [
                'id'      => 'US',
                'name_ru' => 'США',
            ],
            [
                'id'      => 'CA',
                'name_ru' => 'Канада',
            ],
        ],
    ],
    [
        'de',
        [], // No countries for German language
    ],
]);

dataset('languages data', [
    [
        [
            [
                'id'      => 'ru',
                'name_ru' => 'Русский',
            ],
            [
                'id'      => 'en',
                'name_ru' => 'English',
            ],
            [
                'id'      => 'de',
                'name_ru' => 'Deutsch',
            ],
            [
                'id'      => 'fr',
                'name_ru' => 'Français',
            ],
        ],
    ],
]);

dataset('edit customer history data', [
    [
        [
            'id'         => 1,
            'status'     => 2,
            'preview'    => 'Updated preview',
            'text'       => 'Updated full text',
            'commentary' => 'Updated commentary',
            'shops'      => ['RU', 'BY'],
        ],
        true, // Expected success
    ],
    [
        [
            'id'         => 999,
            'status'     => 1,
            'preview'    => 'Non-existent record',
            'text'       => 'This should fail',
            'commentary' => 'Error case',
            'shops'      => ['US'],
        ],
        false, // Expected failure
    ],
]);
