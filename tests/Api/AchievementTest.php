<?php

declare(strict_types=1);

it('achievement api test QUERY', function (string $rpcMethod, array $params): void {

    $result = testRpcCall($rpcMethod, $params);

    echo sprintf("%s %s\n", $rpcMethod, strlen(json_encode($result, JSON_UNESCAPED_UNICODE)));

    expect($result)->not()->toBeEmpty();

})->with('achievementsApiQuery');

// TODO
//it('achievement api test COMMAND', function (string $rpcMethod, array $params): void {
//
//    $result = testRpcCall($rpcMethod, $params);
//
//    echo sprintf("%s %s\n", $rpcMethod, strlen(json_encode($result, JSON_UNESCAPED_UNICODE)));
//
//    expect($result)->not()->toBeEmpty();
//
//})->with('achievementsApiQuery');
