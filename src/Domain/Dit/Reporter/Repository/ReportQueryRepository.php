<?php

declare(strict_types=1);

namespace App\Domain\Dit\Reporter\Repository;

use App\Domain\Dit\Reporter\Entity\Report;
use Database\Connection\ParamMode;
use Database\Connection\ParamType;
use Database\ORM\QueryRepository;
use Generator;

/**
 * @extends QueryRepository<Report>
 */
class ReportQueryRepository extends QueryRepository
{
    protected string $entityName = Report::class;

    public function getReport(int $reportId): Report
    {
        $result = $this->conn->procedure('reporter.preporter.cr_report_details', [
            'o_result' => null,
            'i_report' => $reportId,
        ], [
            'o_result' => [ParamMode::OUT, ParamType::CURSOR],
            'i_report' => [ParamMode::IN, ParamType::INTEGER],
        ]);
        $result = $result['o_result'][0] ?? [];

        $owner = null;
        if ($result['owner']) {
            $owner = [
                'id'    => (int) $result['owner'],
                'name'  => $result['owner_name'],
                'email' => $result['owner_email'],
            ];
        }

        return new Report(
            id: (int) $result['id'],
            name: $result['name'],
            currentUserInUk: (int) $result['current_user_in_uk'],
            data: $result['report_data'],
            owner: $owner,
        );
    }

    public function getReportListFlat(): array
    {
        return $this->conn->procedure('reporter.preporter.cr_my_reports', [
            'o_result' => null,
        ], [
            'o_result' => [ParamMode::OUT, ParamType::CURSOR],
        ])['o_result'] ?? [];
    }

    public function getReportList(): array
    {
        $items = $this->getReportListFlat();

        $tmp = [];
        foreach ($items as $item) {
            $item['id'] = (int) $item['id'];
            $item['report_type'] = (int) $item['report_type'];
            $tmp[$item['id']] = $item + [
                'children' => [],
            ];
        }

        $grouped = [];
        foreach ($items as $item) {
            if (isset($item['parent'], $tmp[$item['parent']])) {
                $tmp[$item['parent']]['children'][] = &$tmp[$item['id']];
                $tmp[$item['parent']]['opened'] = true;
            } else {
                $grouped[] = &$tmp[$item['id']];
            }
        }
        return $grouped;
    }

    public function getReportOwners(array $ids): Generator
    {
        $sql = <<<SQL
            select id, name, active from test.cp_emp where id in (:ids)
        SQL;

        return $this->conn->query($sql, [
            'ids' => $ids,
        ], [
            'ids' => ParamType::ARRAY_INTEGER,
        ]);
    }
}
