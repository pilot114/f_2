<?php

declare(strict_types=1);

use App\System\Command\DecodeJwt;
use App\System\Security\JWT;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;

beforeEach(function (): void {
    $this->jwt = mock(JWT::class);
    $this->command = new DecodeJwt($this->jwt);
});

it('has correct name and description', function (): void {
    expect($this->command->getName())->toBe('system:decodeJwt');
    expect($this->command->getDescription())->toBe('расшифровка jwt');
});

it('executes successfully and outputs decoded jwt', function (): void {
    $jwtToken = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.test';
    $decodedData = [
        'user_id' => 123,
        'email'   => 'test@example.com',
    ];

    $this->jwt
        ->shouldReceive('decode')
        ->with($jwtToken)
        ->once()
        ->andReturn($decodedData);

    $input = new StringInput($jwtToken);
    $output = new BufferedOutput();

    // We need to manually bind the input to the command definition
    $input->bind($this->command->getDefinition());
    $result = $this->command->run($input, $output);

    expect($result)->toBe(Command::SUCCESS);
    $expectedOutput = json_encode($decodedData, JSON_PRETTY_PRINT) . PHP_EOL;
    expect($output->fetch())->toBe($expectedOutput);
});

it('handles empty decoded data', function (): void {
    $jwtToken = 'invalid.jwt.token';
    $decodedData = [];

    $this->jwt
        ->shouldReceive('decode')
        ->with($jwtToken)
        ->once()
        ->andReturn($decodedData);

    $input = new StringInput($jwtToken);
    $output = new BufferedOutput();

    $input->bind($this->command->getDefinition());
    $result = $this->command->run($input, $output);

    expect($result)->toBe(Command::SUCCESS);
    $expectedOutput = json_encode($decodedData, JSON_PRETTY_PRINT) . PHP_EOL;
    expect($output->fetch())->toBe($expectedOutput);
});

it('handles json encoding failure', function (): void {
    $jwtToken = 'test.jwt.token';
    $decodedData = ["\xB1\x31"];  // Invalid UTF-8 sequence to cause json_encode to fail

    $this->jwt
        ->shouldReceive('decode')
        ->with($jwtToken)
        ->once()
        ->andReturn($decodedData);

    $input = new StringInput($jwtToken);
    $output = new BufferedOutput();

    $input->bind($this->command->getDefinition());
    $result = $this->command->run($input, $output);

    expect($result)->toBe(Command::FAILURE);
    expect($output->fetch())->toContain('Failed to encode data to JSON');
});
