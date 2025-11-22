<?php

declare(strict_types=1);

namespace App\Tests\Unit\System\Command;

use App\Common\Service\File\TempFileRegistry;
use App\System\Command\ClipClassWithDepends;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use ReflectionClass;
use ReflectionMethod;

uses(MockeryPHPUnitIntegration::class);

beforeEach(function (): void {
    $this->tempFileRegistry = Mockery::mock(TempFileRegistry::class);
    $this->command = new ClipClassWithDepends($this->tempFileRegistry);
});

afterEach(function (): void {
    Mockery::close();
});

it('извлекает исходный код класса', function (): void {
    $className = 'App\System\Command\CheckAccess';

    $reflection = new ReflectionClass($className);
    $command = new ClipClassWithDepends($this->tempFileRegistry);

    $method = new ReflectionMethod($command, 'getSourceByClassName');
    $method->setAccessible(true);

    $source = $method->invoke($command, $className);

    expect($source)->toBeString()
        ->toContain('namespace App\System\Command')
        ->toContain('class CheckAccess');
});

it('извлекает use statements из исходного кода', function (): void {
    $source = <<<'PHP'
<?php

namespace App\Test;

use App\System\Command\CheckAccess;
use Symfony\Component\Console\Command\Command;
use DateTime;

class TestClass
{
    public function test(): void
    {
    }
}
PHP;

    $command = new ClipClassWithDepends($this->tempFileRegistry);
    $method = new ReflectionMethod($command, 'getUses');
    $method->setAccessible(true);

    $uses = $method->invoke($command, $source);

    expect($uses)->toBeArray()
        ->toContain('App\System\Command\CheckAccess');
});

it('проверяет корректную настройку команды', function (): void {
    $definition = $this->command->getDefinition();

    expect($definition->hasArgument('className'))->toBeTrue()
        ->and($definition->getArgument('className')->isRequired())->toBeTrue()
        ->and($definition->getArgument('className')->getDescription())->toBe('имя класса');
});

it('имеет корректное название и описание', function (): void {
    expect($this->command->getName())->toBe('system:clipClassWithDepends')
        ->and($this->command->getDescription())->toBe('скопировать в буфер обмена класс и его зависимости');
});

it('has required className argument', function (): void {
    $definition = $this->command->getDefinition();

    expect($definition->hasArgument('className'))->toBeTrue()
        ->and($definition->getArgument('className')->isRequired())->toBeTrue();
});

it('correctly extracts use statements from complex code', function (): void {
    $source = <<<'PHP'
<?php

namespace App\Test;

use App\System\Command\CheckAccess;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\HttpFoundation\Request;
use DateTime;
use stdClass;

class TestClass
{
    public function __construct(
        private CheckAccess $checkAccess,
    ) {}

    public function test(): void
    {
        $request = new Request();
        $date = new DateTime();
    }
}
PHP;

    $command = new ClipClassWithDepends($this->tempFileRegistry);
    $method = new ReflectionMethod($command, 'getUses');
    $method->setAccessible(true);

    $uses = $method->invoke($command, $source);

    expect($uses)->toBeArray()
        ->toContain('App\System\Command\CheckAccess');
});

it('filters out interfaces from use statements', function (): void {
    $source = <<<'PHP'
<?php

namespace App\Test;

use App\System\Command\CheckAccess;
use Psr\Log\LoggerInterface;

class TestClass
{
    public function __construct(
        private CheckAccess $checkAccess,
    ) {}
}
PHP;

    $command = new ClipClassWithDepends($this->tempFileRegistry);
    $method = new ReflectionMethod($command, 'getUses');
    $method->setAccessible(true);

    $uses = $method->invoke($command, $source);

    expect($uses)->toBeArray()
        ->toContain('App\System\Command\CheckAccess')
        ->not->toContain('Psr\Log\LoggerInterface');
});

it('returns empty array when no use statements found', function (): void {
    $source = <<<'PHP'
<?php

namespace App\Test;

class TestClass
{
    public function test(): void
    {
    }
}
PHP;

    $command = new ClipClassWithDepends($this->tempFileRegistry);
    $method = new ReflectionMethod($command, 'getUses');
    $method->setAccessible(true);

    $uses = $method->invoke($command, $source);

    expect($uses)->toBeArray()
        ->toBeEmpty();
});

it('returns empty array when source parsing fails', function (): void {
    $source = 'invalid php code {';

    $command = new ClipClassWithDepends($this->tempFileRegistry);
    $method = new ReflectionMethod($command, 'getUses');
    $method->setAccessible(true);

    $uses = $method->invoke($command, $source);

    expect($uses)->toBeArray()
        ->toBeEmpty();
});

it('has getSourceByClassName method', function (): void {
    $reflection = new ReflectionClass(ClipClassWithDepends::class);

    expect($reflection->hasMethod('getSourceByClassName'))->toBeTrue();

    $method = $reflection->getMethod('getSourceByClassName');
    expect($method->isProtected())->toBeTrue();
});

it('has getUses method', function (): void {
    $reflection = new ReflectionClass(ClipClassWithDepends::class);

    expect($reflection->hasMethod('getUses'))->toBeTrue();

    $method = $reflection->getMethod('getUses');
    expect($method->isProtected())->toBeTrue();
});

it('has execute method', function (): void {
    $reflection = new ReflectionClass(ClipClassWithDepends::class);

    expect($reflection->hasMethod('execute'))->toBeTrue();

    $method = $reflection->getMethod('execute');
    expect($method->isProtected())->toBeTrue();
});

it('extends Command class', function (): void {
    $reflection = new ReflectionClass(ClipClassWithDepends::class);

    expect($reflection->getParentClass()->getName())->toBe('Symfony\Component\Console\Command\Command');
});

it('has private tempFileRegistry property', function (): void {
    $reflection = new ReflectionClass(ClipClassWithDepends::class);

    expect($reflection->hasProperty('tempFileRegistry'))->toBeTrue();

    $property = $reflection->getProperty('tempFileRegistry');
    expect($property->isPrivate())->toBeTrue();
});
