<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Finance\KPI\UseCase;

use App\Domain\Finance\Kpi\Entity\FinEmployee;
use App\Domain\Finance\Kpi\Repository\KpiCommandRepository;
use App\Domain\Finance\Kpi\Repository\KpiQueryRepository;
use App\Domain\Finance\Kpi\Service\KpiEmailer;
use App\Domain\Finance\Kpi\Service\KpiExcel;
use App\Domain\Finance\Kpi\UseCase\SendToTreasuryUseCase;
use App\Domain\Portal\Security\Entity\SecurityUser;
use App\Domain\Portal\Security\Repository\SecurityQueryRepository;
use Database\ORM\QueryRepositoryInterface;
use Illuminate\Support\Collection;
use Mockery;
use Mockery\LegacyMockInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

beforeEach(function (): void {
    $this->read = Mockery::mock(KpiQueryRepository::class);
    $this->write = Mockery::mock(KpiCommandRepository::class);
    $this->secRepo = Mockery::mock(SecurityQueryRepository::class);
    $this->finEmpRepo = Mockery::mock(QueryRepositoryInterface::class);
    $this->excel = Mockery::mock(KpiExcel::class);
    $this->email = Mockery::mock(KpiEmailer::class);

    $this->useCase = new SendToTreasuryUseCase(
        $this->read,
        $this->write,
        $this->secRepo,
        $this->finEmpRepo,
        $this->excel,
        $this->email
    );

    $this->currentUser = createSecurityUser(1);
    $this->departmentName = 'Test Department';
});

afterEach(function (): void {
    Mockery::close();
});

function mockUploadedFile(): UploadedFile
{
    // Create a real temporary file for testing
    $tempFile = tmpfile();
    $tempPath = stream_get_meta_data($tempFile)['uri'];
    return new UploadedFile($tempPath, 'test.xlsx', null, null, true);
}

function mockFinEmpConversion(array $cpIds, array $finEmpIds, LegacyMockInterface $finEmpRepo): void
{
    $collection = new Collection();
    foreach ($finEmpIds as $id) {
        $finEmp = Mockery::mock(FinEmployee::class)->shouldReceive('getFinEmpId')->andReturn($id)->getMock();
        $collection->push($finEmp);
    }

    $finEmpRepo->shouldReceive('findBy')->once()->with([
        'cp_id' => $cpIds,
    ])->andReturn($collection);
}

function getDefaultExportData(): array
{
    return [
        [
            'id'                 => 1,
            'last_name'          => 'Иванов',
            'first_name'         => 'Иван',
            'middle_name'        => 'Иванович',
            'cfo_contract'       => 'Contract1',
            'cfo_name'           => 'CFO1',
            'kpi'                => 95,
            'two_month_bonus'    => null,
            'four_months_bonus'  => null,
            'enterprise_id'      => 424242,
            'enterprise_name'    => 'Enterprise1',
            'enterprise_country' => '1',
            'dt'                 => '01.01.2025',
        ],
        [
            'id'                 => 2,
            'last_name'          => 'Петров',
            'first_name'         => 'Петр',
            'middle_name'        => 'Петрович',
            'cfo_contract'       => 'Contract2',
            'cfo_name'           => 'CFO2',
            'kpi'                => null,
            'two_month_bonus'    => 90,
            'four_months_bonus'  => null,
            'enterprise_id'      => 424242,
            'enterprise_name'    => 'Enterprise2',
            'enterprise_country' => '1',
            'dt'                 => '01.01.2025',
        ],
    ];
}

function setupCommonMocks(
    LegacyMockInterface $read,
    LegacyMockInterface $write,
    LegacyMockInterface $secRepo,
    LegacyMockInterface $excel,
    LegacyMockInterface $email,
    SecurityUser        $currentUser,
    string              $departmentName,
    array               $finEmpIds,
    array               $exportData,
    bool                $hasSuperBoss = false,
    int                 $clearCalls = 2
): void {
    $emails = [
        424242 => 'response_email@mail.com',
    ];
    $read->shouldReceive('getResponsibleEmailsByEnterprises')->once()->with([424242])->andReturn($emails);
    $secRepo->shouldReceive('hasCpAction')->once()->with($currentUser->id, 'accured_kpi.accured_kpi_superboss')->andReturn($hasSuperBoss);
    $read->shouldReceive('whoDeputied')->once()->with($currentUser->id)->andReturn([]);
    $read->shouldReceive('dataForExport')->once()->with($finEmpIds)->andReturn($exportData);
    $secRepo->shouldReceive('getDepartmentNameWhereBoss')->once()->with($currentUser->id)->andReturn($departmentName);
    $excel->shouldReceive('clear')->times($clearCalls)->andReturnSelf();
    $excel->shouldReceive('setName')->times($clearCalls)->andReturnSelf();
    $excel->shouldReceive('setContent')->times($clearCalls)->andReturnSelf();
    $excel->shouldReceive('getFile')->times($clearCalls)->andReturn(mockUploadedFile());
    $email->shouldReceive('send')->once()->with(array_values($emails), Mockery::type('array'), $departmentName, $currentUser->name);
    $write->shouldReceive('sendToTreasury')->once()->with($finEmpIds)->andReturn(true);
}

it('sends data to treasury successfully', function (): void {
    $finEmpIds = [101, 102];
    $exportData = getDefaultExportData();

    setupCommonMocks(
        $this->read,
        $this->write,
        $this->secRepo,
        $this->excel,
        $this->email,
        $this->currentUser,
        $this->departmentName,
        $finEmpIds,
        $exportData
    );

    $this->read->shouldReceive('findEmpForExport')->once()->with($this->currentUser->id, null, false)->andReturn($finEmpIds);

    expect($this->useCase->sendToTreasury($this->currentUser))->toBeTrue();
});

it('includes super boss data when user has permission', function (): void {
    $finEmpIds = [101, 102];
    $bossFinEmpIds = [201, 202];
    $mergedFinEmpIds = [...$finEmpIds, ...$bossFinEmpIds];
    $exportData = [getDefaultExportData()[0]];

    setupCommonMocks(
        $this->read,
        $this->write,
        $this->secRepo,
        $this->excel,
        $this->email,
        $this->currentUser,
        $this->departmentName,
        $mergedFinEmpIds,
        $exportData,
        true,
        clearCalls: 1
    );

    $this->read->shouldReceive('findEmpForExport')->once()->with($this->currentUser->id, null, false)->andReturn($finEmpIds);
    $this->read->shouldReceive('bossListForExport')->once()->with(null)->andReturn([201, 202]);
    mockFinEmpConversion([201, 202], $bossFinEmpIds, $this->finEmpRepo);

    expect($this->useCase->sendToTreasury($this->currentUser))->toBeTrue();
});

it('filters user list when userIds are provided', function (): void {
    $finEmpIds = [101, 102, 103];
    $userIds = [5, 6];
    $finEmpIdsFiltered = [101, 102];
    $exportData = [getDefaultExportData()[0]];

    setupCommonMocks(
        $this->read,
        $this->write,
        $this->secRepo,
        $this->excel,
        $this->email,
        $this->currentUser,
        $this->departmentName,
        $finEmpIdsFiltered,
        $exportData,
        clearCalls: 1
    );

    $this->read->shouldReceive('findEmpForExport')->once()->with($this->currentUser->id, null, false)->andReturn($finEmpIds);
    mockFinEmpConversion($userIds, $finEmpIdsFiltered, $this->finEmpRepo);

    expect($this->useCase->sendToTreasury($this->currentUser, null, false, $userIds))->toBeTrue();
});

it('filters by query when provided', function (): void {
    $searchQuery = 'Иванов';
    $finEmpIds = [101];
    $exportData = [getDefaultExportData()[0]];

    setupCommonMocks(
        $this->read,
        $this->write,
        $this->secRepo,
        $this->excel,
        $this->email,
        $this->currentUser,
        $this->departmentName,
        $finEmpIds,
        $exportData,
        clearCalls: 1
    );
    $this->read->shouldReceive('findEmpForExport')->once()->with($this->currentUser->id, $searchQuery, false)->andReturn($finEmpIds);

    expect($this->useCase->sendToTreasury($this->currentUser, $searchQuery))->toBeTrue();
});

it('throws exception when no data found', function (): void {
    $finEmpIds = [101, 102];

    $this->secRepo->shouldReceive('hasCpAction')->once()->with($this->currentUser->id, 'accured_kpi.accured_kpi_superboss')->andReturn(false);
    $this->read->shouldReceive('findEmpForExport')->once()->with($this->currentUser->id, null, false)->andReturn($finEmpIds);
    $this->read->shouldReceive('dataForExport')->once()->with($finEmpIds)->andReturn([]);

    expect(fn () => $this->useCase->sendToTreasury($this->currentUser))
        ->toThrow(NotFoundHttpException::class, 'Не найдено данных для отправки при запрашиваемых параметрах');
});

it('correctly sorts data by month, type, and country', function (): void {
    $finEmpIds = [101, 102, 103];
    $exportData = [
        ...getDefaultExportData(),
        [
            'id'                 => 3,
            'last_name'          => 'Сидоров',
            'first_name'         => 'Сидор',
            'middle_name'        => 'Сидорович',
            'cfo_contract'       => 'Contract3',
            'cfo_name'           => 'CFO3',
            'kpi'                => null,
            'two_month_bonus'    => null,
            'four_months_bonus'  => 85,
            'enterprise_id'      => 424242,
            'enterprise_name'    => 'Enterprise3',
            'enterprise_country' => '2',
            'dt'                 => '01.02.2025',
        ],
    ];

    setupCommonMocks(
        $this->read,
        $this->write,
        $this->secRepo,
        $this->excel,
        $this->email,
        $this->currentUser,
        $this->departmentName,
        $finEmpIds,
        $exportData,
        clearCalls: 3
    );

    $this->read->shouldReceive('findEmpForExport')->once()->with($this->currentUser->id, null, false)->andReturn($finEmpIds);

    expect($this->useCase->sendToTreasury($this->currentUser))->toBeTrue();
});
