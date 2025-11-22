<?php

declare(strict_types=1);

namespace App\Tests\Datasets;

use Generator;

function getQueries(): array
{
    return [
        'hr.achievements.getCategories'   => [],
        'hr.achievements.getCategoryById' => [
            [
                'id' => getMaxIdFromTable('TEST.CP_EA_CATEGORIES'),
            ],
        ],
        'hr.achievements.checkCategoryNameExist' => [
            [
                'name' => 'Test Category',
            ],
        ],
        'hr.achievements.getAchievementCards'    => [],
        'hr.achievements.getAchievementCardById' => [
            [
                'id' => getMaxIdFromTable('TEST.CP_EA_ACHIEVEMENT_CARDS'),
            ],
        ],
        'hr.achievements.checkCardNameExist' => [
            [
                'name' => 'Test Achievement',
            ],
        ],
        'hr.achievements.getEmployeeAchievements' => [],
        'hr.achievements.getAchievementUnlockers' => [
            [
                'achievementId' => getMaxIdFromTable('TEST.CP_EA_ACHIEVEMENT_CARDS'),
            ],
        ],
        'hr.achievements.getCategoriesColors' => [],
        'hr.achievements.getImages'           => [],
        'hr.achievements.getForOfficeMap'     => [
            [
                'userId' => getMaxIdFromTable('TEST.CP_EMP'),
            ],
        ],
    ];
}

function getCommands(): array
{
    return [
        // category
        'hr.achievements.createCategory' => [
            [
                'name'       => 'Test Category ' . time(),
                'colorsId'   => getMaxIdFromTable('TEST.CP_EA_COLORS'),
                'isPersonal' => false,
            ],
        ],
        'hr.achievements.updateCategory' => [
            [
                'id'         => getMaxIdFromTable('TEST.CP_EA_CATEGORIES'),
                'name'       => 'Updated Category ' . time(),
                'colorId'    => getMaxIdFromTable('TEST.CP_EA_COLORS'),
                'isPersonal' => true,
            ],
        ],

        // achievements
        'hr.achievements.createAchievementCard' => [
            [
                'categoriesId'   => getMaxIdFromTable('TEST.CP_EA_CATEGORIES'),
                'name'           => 'Test Achievement ' . time(),
                'imageLibraryId' => getMaxIdFromTable('TEST.CP_EA_IMAGE_LIBRARY'),
                'description'    => 'Test achievement description',
            ],
        ],
        'hr.achievements.updateAchievementCard' => [
            [
                'id'             => getMaxIdFromTable('TEST.CP_EA_ACHIEVEMENT_CARDS'),
                'categoriesId'   => getMaxIdFromTable('TEST.CP_EA_CATEGORIES'),
                'name'           => 'Updated Achievement ' . time(),
                'imageLibraryId' => getMaxIdFromTable('TEST.CP_EA_IMAGE_LIBRARY'),
                'description'    => 'Updated description',
            ],
        ],
        'hr.achievements.deleteAchievementCardById' => [
            [
                'id' => getMaxIdFromTable('TEST.CP_EA_ACHIEVEMENT_CARDS'),
            ],
        ],

        // achievement + employee
        'hr.achievements.unlockEmployeeAchievements' => [
            [
                'achievementId' => getMaxIdFromTable('TEST.CP_EA_ACHIEVEMENT_CARDS'),
                'userId'        => getMaxIdFromTable('TEST.CP_EMP'),
                'receiveDate'   => '2025-09-15T00:00:00+00:00',
            ],
        ],
        'hr.achievements.editEmployeeAchievement' => [
            [
                'id'          => getMaxIdFromTable('test.cp_ea_employee_achievments'),
                'userId'      => getMaxIdFromTable('TEST.CP_EMP'),
                'receiveDate' => '2025-09-16',
            ],
        ],
        'hr.achievements.deleteEmployeeAchievements' => [
            [
                'recordId' => getMaxIdFromTable('test.cp_ea_employee_achievments'),
            ],
        ],
        'hr.achievements.unlockFromExcel' => [
            [
                'fileId' => 1, // TODO
                'cardId' => getMaxIdFromTable('TEST.CP_EA_ACHIEVEMENT_CARDS'),
            ],
        ],
    ];
}

function convert(callable $source): Generator
{
    foreach ($source() as $rpcMethod => $batch) {
        if ($batch === []) {
            yield [$rpcMethod, $batch];
        }
        foreach ($batch as $params) {
            yield [$rpcMethod, $params];
        }
    }
}

dataset('achievementsApiQuery', convert(getQueries(...)));
dataset('achievementsApiCommand', convert(getCommands(...)));
