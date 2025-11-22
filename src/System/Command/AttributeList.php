<?php

declare(strict_types=1);

namespace App\System\Command;

use App\Common\Attribute\CpAction;
use App\Common\Attribute\CpMenu;
use App\System\RPC\Attribute\RpcMethodLoader;
use App\System\Security\Attribute\CpActionLoader;
use App\System\Security\Attribute\CpMenuLoader;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'system:attributeList', description: 'список зарегистрированных аттрибутов')]
class AttributeList extends Command
{
    public function __construct(
        private RpcMethodLoader $rpcLoader,
        private CpActionLoader $cpActionLoader,
        private CpMenuLoader $cpMenuLoader,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('RPC, CpAction, CpMenu');
        $tableRpc = new Table($output);

        $tableRpc->setHeaders(['rpc name', 'cp_access expression', 'cp_menu expression'])->setStyle('box');

        foreach ($this->rpcLoader->load() as $fqn => $rpc) {
            $cpAction = $this->cpActionLoader->loadByFqn($fqn);
            $cpMenu = $this->cpMenuLoader->loadByFqn($fqn);

            $appendCpAction = $cpAction instanceof CpAction ? [$cpAction->expression] : [];
            $appendCpMenu = $cpMenu instanceof CpMenu ? [$cpMenu->expression] : [];
            $tableRpc->addRow([$rpc->name, ...$appendCpAction, ...$appendCpMenu]);
        }

        $tableRpc->render();
        return Command::SUCCESS;
    }
}
