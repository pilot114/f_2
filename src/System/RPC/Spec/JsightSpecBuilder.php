<?php

declare(strict_types=1);

namespace App\System\RPC\Spec;

use App\Common\Attribute\RpcMethod;
use App\System\RPC\RpcServer;
use Generator;

readonly class JsightSpecBuilder implements SpecBuilderInterface
{
    public function __construct(
        /** @var Generator<string, RpcMethod> */
        private Generator $methods,
    ) {
    }

    public function build(): string
    {
        $spec = "JSIGHT 0.3\n\n";
        $spec .= "URL " . RpcServer::RPC_URL . "\n";
        $spec .= "\tProtocol json-rpc-2.0\n";

        foreach ($this->methods as $method) {
            $spec .= "\tMethod $method->name // $method->summary\n";

            foreach ($method->examples as $example) {
                $spec .= "\t\tParams\n";
                $spec .= "\t\t\t{\n";
                foreach ($example['params'] ?? [] as $paramName => $param) {
                    if (is_string($param)) {
                        $prettyValue = json_encode(json_decode($param), JSON_PRETTY_PRINT);
                        if ($prettyValue === false) {
                            continue;
                        }
                        $spec .= "\t\t\t\t\"$paramName\":\n";
                        foreach (explode("\n", $prettyValue) as $line) {
                            $spec .= "\t\t\t\t$line\n";
                        }
                    }
                }
                $spec .= "\t\t\t}\n";

                if (!isset($example['result'])) {
                    continue;
                }
                $spec .= "\t\tResult\n";
                $prettyValue = json_encode(json_decode($example['result']), JSON_PRETTY_PRINT);
                if ($prettyValue === false) {
                    continue;
                }
                foreach (explode("\n", $prettyValue) as $line) {
                    $spec .= "\t\t\t$line\n";
                }
            }
        }
        return $spec;
    }
}
