<?php

declare(strict_types=1);

namespace App\System\Command\MockManagement;

use App\System\RPC\Attribute\RpcMethodLoader;
use App\System\RPC\Spec\OpenRpcMockSpecBuilder;
use Symfony\Component\Console\Command\Command;

abstract class AbstractMockCommand extends Command
{
    protected OpenRpcMockSpecBuilder $mockService;

    public function __construct(
        private readonly RpcMethodLoader $loader,
    ) {
        parent::__construct();

        $methods = $this->loader->load();
        $schemas = $this->loader->getSchemas();
        $this->mockService = new OpenRpcMockSpecBuilder($methods, $schemas, 'dev');
    }
}
