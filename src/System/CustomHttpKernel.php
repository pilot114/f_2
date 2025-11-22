<?php

declare(strict_types=1);

namespace App\System;

use App\System\RPC\Attribute\RpcMethodLoader;
use App\System\RPC\RpcServer;
use App\System\RPC\Spec\JsightSpecBuilder;
use App\System\RPC\Spec\OpenRpcMockSpecBuilder;
use App\System\RPC\Spec\OpenRpcSpecBuilder;
use App\System\RPC\Spec\PostmanSpecBuilder;
use App\System\RPC\Spec\Repository\MockSpecRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;
use Symfony\Component\HttpKernel\Event\FinishRequestEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Перехватывает HTTP запросы: /api/v2/rpc и /api/v2/mcp
 */
class CustomHttpKernel extends HttpKernel
{
    private const DEFAULT_SPEC_TYPE = 'openRpc';

    public function __construct(
        EventDispatcherInterface               $dispatcher,
        ControllerResolverInterface            $resolver,
        private readonly RpcServer             $rpcServer,
        private readonly McpServer             $mcpServer,
        private readonly RpcMethodLoader       $loader,
        private MockSpecRepository             $mockSpecRepository,
        private RouterInterface                $router,
        private readonly string                $env,
    ) {
        parent::__construct($dispatcher, $resolver);
    }

    public function handle(Request $request, int $type = self::MAIN_REQUEST, bool $catch = true): Response
    {
        if ($request->getPathInfo() === $this->mcpServer::MCP_URL) {
            $this->mcpServer->register();
            return $this->mcpServer->handle($request);
        }

        // If not an RPC request, use the default handler
        if ($request->getPathInfo() !== $this->rpcServer::RPC_URL || $type !== self::MAIN_REQUEST) {
            return parent::handle($request, $type, $catch);
        }

        // for skip default routing
        $request->attributes->set('_controller', true);

        $response = $this->dispatchRequest($request, $type);
        if ($response instanceof Response) {
            return $response;
        }

        if ($request->isMethod('GET')) {
            return $this->handleRpcSpec($request);
        }

        if ($request->isMethod('POST')) {
            return $this->handleRpcRequest($request, $type);
        }

        throw new MethodNotAllowedHttpException(
            ['GET', 'POST'],
            sprintf('HTTP метод %s не поддерживается', $request->getMethod())
        );
    }

    private function handleRpcRequest(Request $request, int $type): Response
    {
        $result = $this->rpcServer
            ->setContext($this, $this->dispatcher, $type)
            ->load()
            ->handleRequest($request)
        ;
        return new JsonResponse($result);
    }

    private function handleRpcSpec(Request $request): Response
    {
        $specType = (string) $request->query->get('specType', self::DEFAULT_SPEC_TYPE);
        $tags = (string) $request->query->get('tags');
        $tags = $tags === '' ? [] : explode(',', $tags);
        $method = (string) $request->query->get('method') ?: null;
        $includeMocks = (bool) $request->query->get('includeMocks', false);

        $methods = $this->loader->loadWithFilter($tags, $method);
        $schemas = $this->loader->getSchemas();

        if ($includeMocks && $specType === self::DEFAULT_SPEC_TYPE) {
            $specBuilder = new OpenRpcMockSpecBuilder($methods, $schemas, $this->env);
            $specBuilder->setMockRepository($this->mockSpecRepository);
            return new JsonResponse($specBuilder->build());
        }

        return match ($specType) {
            'jSight' => new Response(
                (new JsightSpecBuilder($methods))->build(),
                headers: [
                    'Content-Type' => 'text/text; charset=UTF-8',
                ]
            ),
            'postman' => new JsonResponse((new PostmanSpecBuilder($methods, $this->router))->build()),
            default   => new JsonResponse((new OpenRpcSpecBuilder($methods, $schemas, $this->env))->build()),
        };
    }

    private function dispatchRequest(Request $request, int $type): ?Response
    {
        $event = new RequestEvent($this, $request, $type);

        $this->dispatcher->dispatch($event, KernelEvents::REQUEST);

        $response = $event->getResponse();
        if ($response instanceof Response) {
            $event = new ResponseEvent($this, $request, $type, $response);
            $this->dispatcher->dispatch($event, KernelEvents::RESPONSE);
            $this->dispatcher->dispatch(new FinishRequestEvent($this, $request, $type), KernelEvents::FINISH_REQUEST);
            return $event->getResponse();
        }
        return null;
    }
}
