<?php

declare(strict_types=1);

namespace App\System\Command;

use App\System\Security\JWT;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'system:decodeJwt', description: 'расшифровка jwt')]
class DecodeJwt extends Command
{
    public function __construct(
        private JWT $decoder,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('jwt', InputArgument::REQUIRED, 'jwt')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string $jwt */
        $jwt = $input->getArgument('jwt');

        $data = $this->decoder->decode($jwt);
        $prettyData = json_encode($data, flags: JSON_PRETTY_PRINT);
        if ($prettyData !== false) {
            $output->writeln($prettyData);
            return Command::SUCCESS;
        }

        $output->writeln('Failed to encode data to JSON');
        return Command::FAILURE;
    }
}
