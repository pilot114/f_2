<?php

declare(strict_types=1);

use App\Domain\Dit\Reporter\Command\TestExportReportCommand;
use App\Domain\Dit\Reporter\Message\ExportReportMessage;
use App\Domain\Dit\Reporter\Message\ExportReportMessageHandler;
use App\Domain\Dit\Reporter\Repository\ReportQueryRepository;
use App\Domain\Dit\Reporter\Service\ReporterEmailer;
use App\Domain\Dit\Reporter\Service\ReporterExcel;
use App\Domain\Dit\Reporter\UseCase\ExecuteReportUseCase;
use App\Domain\Portal\Security\Entity\SecurityUser;
use App\Domain\Portal\Security\Repository\SecurityQueryRepository;
use Database\Connection\CpConnection;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

beforeEach(function (): void {
    $this->bus = Mockery::mock(MessageBusInterface::class);
    $this->securityQueryRepository = Mockery::mock(SecurityQueryRepository::class);

    // Create mock dependencies for handler
    $executeReportUseCase = Mockery::mock(ExecuteReportUseCase::class);
    $reporterExcel = Mockery::mock(ReporterExcel::class);
    $reporterEmailer = Mockery::mock(ReporterEmailer::class);
    $reportQueryRepository = Mockery::mock(ReportQueryRepository::class);
    $logger = Mockery::mock(LoggerInterface::class);
    $conn = Mockery::mock(CpConnection::class);

    // Real handler instance (readonly class cannot be mocked)
    $this->handler = new ExportReportMessageHandler(
        $executeReportUseCase,
        $reporterExcel,
        $reporterEmailer,
        $this->securityQueryRepository,
        $reportQueryRepository,
        $logger,
        $conn
    );

    $this->command = new TestExportReportCommand(
        $this->bus,
        $this->securityQueryRepository,
        $this->handler
    );

    $this->commandTester = new CommandTester($this->command);
});

afterEach(function (): void {
    Mockery::close();
});

it('executes command in async mode successfully', function (): void {
    $user = new SecurityUser(
        id: 4026,
        name: 'Test User',
        email: 'test@example.com',
        login: 'test_user',
    );

    $this->securityQueryRepository
        ->shouldReceive('find')
        ->once()
        ->with(4026)
        ->andReturn($user);

    $this->bus
        ->shouldReceive('dispatch')
        ->once()
        ->with(Mockery::type(ExportReportMessage::class))
        ->andReturn(new Envelope(new ExportReportMessage(
            reportId: 12400,
            input: [
                'ds' => '01.10.2025',
                'de' => '01.11.2025',
            ],
            userId: 4026,
            userEmail: 'test@example.com'
        )));

    $exitCode = $this->commandTester->execute([
        'reportId' => '12400',
        'userId'   => '4026',
        '--input'  => '{"ds": "01.10.2025", "de": "01.11.2025"}',
    ]);

    expect($exitCode)->toBe(Command::SUCCESS)
        ->and($this->commandTester->getDisplay())->toContain('Сообщение отправлено в очередь!')
        ->and($this->commandTester->getDisplay())->toContain('Report ID: 12400')
        ->and($this->commandTester->getDisplay())->toContain('User ID: 4026')
        ->and($this->commandTester->getDisplay())->toContain('test@example.com');
});

it('executes command in sync mode successfully', function (): void {
    // Skip this test because it requires real handler execution with all dependencies
    // This is better suited for integration tests
})->skip('Requires full handler integration');

it('fails when user not found', function (): void {
    $this->securityQueryRepository
        ->shouldReceive('find')
        ->once()
        ->with(9999)
        ->andReturn(null);

    $exitCode = $this->commandTester->execute([
        'reportId' => '12400',
        'userId'   => '9999',
    ]);

    expect($exitCode)->toBe(Command::FAILURE)
        ->and($this->commandTester->getDisplay())->toContain('Пользователь с ID 9999 не найден');
});

it('fails when input JSON is invalid', function (): void {
    $exitCode = $this->commandTester->execute([
        'reportId' => '12400',
        'userId'   => '4026',
        '--input'  => '{invalid json}',
    ]);

    expect($exitCode)->toBe(Command::FAILURE)
        ->and($this->commandTester->getDisplay())->toContain('Ошибка парсинга JSON');
});

it('uses empty object as default input', function (): void {
    $user = new SecurityUser(
        id: 4026,
        name: 'Test User',
        email: 'test@example.com',
        login: 'test_user',
    );

    $this->securityQueryRepository
        ->shouldReceive('find')
        ->once()
        ->with(4026)
        ->andReturn($user);

    $this->bus
        ->shouldReceive('dispatch')
        ->once()
        ->with(Mockery::on(function (ExportReportMessage $message): bool {
            return $message->input === [];
        }))
        ->andReturn(new Envelope(new ExportReportMessage(
            reportId: 12400,
            input: [],
            userId: 4026,
            userEmail: 'test@example.com'
        )));

    $exitCode = $this->commandTester->execute([
        'reportId' => '12400',
        'userId'   => '4026',
    ]);

    expect($exitCode)->toBe(Command::SUCCESS);
});
