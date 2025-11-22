<?php

declare(strict_types=1);

namespace App\System\Command;

use App\Domain\Portal\Security\Repository\SecurityQueryRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'system:checkAccess', description: 'проверка прав доступа')]
class CheckAccess extends Command
{
    public function __construct(
        private readonly SecurityQueryRepository $repo,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('code', InputArgument::REQUIRED, 'код права доступа, например: accured_kpi.accured_kpi_superboss')
            ->addArgument('users', InputArgument::REQUIRED, 'список id из test.cp_emp через запятую')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string $code */
        $code = $input->getArgument('code');
        /** @var string $users */
        $users = $input->getArgument('users');
        $users = explode(',', $users);

        $table = new Table($output);
        $table->setHeaderTitle("CpAction: $code")->setHeaders(['user', 'access'])->setStyle('box');
        foreach ($users as $userId) {
            $user = $this->repo->findOneBy([
                'id' => $userId,
            ]);
            if ($user === null) {
                $output->writeln("<error>Не найден пользователь с id $userId</error>");
                return Command::FAILURE;
            }
            if (is_numeric($code)) {
                $any = $this->repo->hasPermission((int) $userId, 'cp_action', (int) $code) ? '+' : '-';
            } else {
                $any = $this->repo->hasCpAction((int) $userId, $code) ? '+' : '-';
            }

            $userTitle = "$userId $user->name";
            $table->addRow([$userTitle, $any]);
        }
        $table->render();

        return Command::SUCCESS;
    }
}
