<?php

declare(strict_types=1);

use App\Common\DTO\FilterOption;
use App\Domain\Events\Rewards\Entity\Nomination;
use App\Domain\Events\Rewards\Entity\Program;

dataset('group list', [
    [
        [
            [
                'country' => FilterOption::Q_ANY,
                'search'  => 'Категория наград - тест',
                'status'  => false,
            ],
            [
                'country' => FilterOption::Q_NONE,
                'search'  => null,
                'status'  => true,
            ],
            [
                'country' => FilterOption::Q_SOME,
                'search'  => null,
                'status'  => true,
            ],
            [
                'country' => 1,
                'search'  => null,
                'status'  => true,
            ],
        ],
        [
            [
                'id'                                                 => "1",
                'name'                                               => "Группа программ - тест",
                'programs_id'                                        => "9319499",
                'programs_name'                                      => "Летнее промо для США",
                'programs_nominations_id'                            => "9394261",
                'programs_nominations_name'                          => "Летнее промо для США 50$",
                'programs_nominations_rewards_id'                    => "10283054",
                'programs_nominations_rewards_name'                  => "Денежная премия",
                'programs_nominations_rewards_product_id'            => 1,
                'programs_nominations_rewards_commentary'            => null,
                'programs_nominations_rewards_statuses_id'           => "14",
                'programs_nominations_rewards_statuses_status'       => "1",
                'programs_nominations_rewards_statuses_country_id'   => "1",
                'programs_nominations_rewards_statuses_country_name' => "Россия",
            ],
            [
                'id'                                                 => "1",
                'name'                                               => "Группа программ - тест",
                'programs_id'                                        => "9319499",
                'programs_name'                                      => "Летнее промо для США",
                'programs_nominations_id'                            => "9394261",
                'programs_nominations_name'                          => "Летнее промо для США 50$",
                'programs_nominations_rewards_id'                    => "10283054",
                'programs_nominations_rewards_name'                  => "Денежная премия",
                'programs_nominations_rewards_product_id'            => 1,
                'programs_nominations_rewards_commentary'            => null,
                'programs_nominations_rewards_statuses_id'           => "15",
                'programs_nominations_rewards_statuses_status'       => "1",
                'programs_nominations_rewards_statuses_country_id'   => "9",
                'programs_nominations_rewards_statuses_country_name' => "Азербайджан",
            ],
        ],
    ],
]);

dataset('country list',
    [
        [
            [1, 9],
            [
                [
                    'id'   => "1",
                    'name' => "Россия",
                ],
                [
                    'id'   => "9",
                    'name' => "Азербайджан",
                ],
            ],
            false,
        ],
        [
            [1, 9999],
            [
                [
                    'id'   => "1",
                    'name' => "Россия",
                ],
            ],
            true,
        ],
    ]
);

dataset('programs list',
    [
        [
            [1, 2],
            [
                [
                    'id'   => "1",
                    'name' => "Тестовая программа 1",
                ],
                [
                    'id'   => "2",
                    'name' => "Тестовая программа 2",
                ],
            ],
            false,
        ],
        [
            [1, 9999],
            [
                [
                    'id'   => "1",
                    'name' => "Тестовая программа 1",
                ],
            ],
            true,
        ],
    ]
);

dataset('rewards with programs list',
    [
        [
            [
                [
                    'id'         => 1,
                    'name'       => 'Награда 1',
                    'productId'  => 1,
                    'nomination' => new Nomination(1,'Номинация 1',new Program(1, 'Программа 1')),
                ],
                [
                    'id'         => 2,
                    'name'       => 'Награда 2',
                    'productId'  => 2,
                    'nomination' => new Nomination(2,'Номинация 2',new Program(2, 'Программа 2')),
                ],
            ],
            [
                [
                    'id'   => 1,
                    'name' => 'Тестовая программа',
                ],
            ],
            false,
        ],
        [
            [
                [
                    'id'         => 1,
                    'name'       => 'Награда 1',
                    'productId'  => 1,
                    'nomination' => new Nomination(1,'Номинация 1',new Program(1, 'Программа 1')),
                ],
                [
                    'id'         => 2,
                    'name'       => 'Награда 2',
                    'productId'  => 2,
                    'nomination' => new Nomination(2,'Номинация 2',new Program(2, 'Программа 2')),
                ],
            ],
            [],
            true,
        ],
    ]
);

dataset('rewards by ids',
    [
        [
            [
                [
                    'id'         => "1",
                    'name'       => 'Награда 1',
                    'product_id' => '1',
                ],
                [
                    'id'         => "2",
                    'name'       => 'Награда 2',
                    'product_id' => '2',
                ],
            ],
            [1, 2],
            false,
        ],
        [
            [
                [
                    'id'         => 1,
                    'name'       => 'Награда 1',
                    'product_id' => '1',
                ],
            ],
            [1, 999],
            true,
        ],
    ]
);

dataset('reward by id',
    [
        [
            [
                [
                    'id'         => "1",
                    'name'       => 'Награда 1',
                    'product_id' => '1',
                ],
            ],
            1,
            false,
        ],
        [
            [],
            999,
            true,
        ],
    ]
);
