<?php

declare(strict_types=1);

namespace App\System\RPC;

use App\Common\Attribute\RpcMethod;
use App\Common\DTO\Titleable;
use App\Common\Exception\DomainException;
use App\System\CustomHttpKernel;
use App\System\Exception\BadRequestHttpExceptionWithViolations;
use App\System\OracleErrorHandler;
use App\System\RPC\Attribute\RpcMethodLoader;
use App\System\Security\AuthUserChecker;
use BackedEnum;
use CuyZ\Valinor\MapperBuilder;
use CuyZ\Valinor\Normalizer\Format;
use Database\EntityNotFoundDatabaseException;
use Doctrine\DBAL\Driver\Exception as DriverException;
use ReflectionMethod;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ArgumentResolverInterface;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Throwable;
use UnitEnum;
use function Sentry\captureException;

class RpcServer
{
    public const RPC_URL = '/api/v2/rpc';

    protected const METHOD_NOT_FOUND = -32601;
    protected const INTERNAL = -32603;
    protected const INVALID_JSON = -32700;
    protected const INVALID_REQUEST = -32600;

    /** @var array<RpcMethod> */
    protected array $methods;

    protected HttpKernelInterface $kernel;
    protected EventDispatcherInterface $dispatcher;
    protected int $requestType;

    public function __construct(
        private readonly ArgumentResolverInterface   $argumentResolver,
        private readonly ControllerResolverInterface $resolver,
        private readonly RpcMethodLoader             $loader,
        private readonly AuthUserChecker             $authUserChecker,
        private readonly string                      $env,
        private readonly ?ValidatorInterface         $validator = null,
    ) {
    }

    public function load(): self
    {
        foreach ($this->loader->load() as $rpc) {
            $this->methods[$rpc->name] = $rpc;
        }
        return $this;
    }

    public function setContext(CustomHttpKernel $kernel, EventDispatcherInterface $dispatcher, int $type): self
    {
        $this->kernel = $kernel;
        $this->dispatcher = $dispatcher;
        $this->requestType = $type;
        return $this;
    }

    public function handleRequest(Request $request): array
    {
        try {
            $payload = json_decode($request->getContent(), true);

            if ($payload === null) {
                return $this->buildErrorResponse(self::INVALID_JSON, 'Невалидный JSON');
            }
            /** @var array $payload */
            $isAssocArray = array_filter(array_keys($payload), is_string(...)) !== [];

            if ($isAssocArray) {
                return $this->handleSingleRPC($payload, $request);
            }

            $result = [];
            foreach ($payload as $item) {
                $result[] = $this->handleSingleRPC($item, $request);
            }
            return $result;
        } catch (Throwable $e) {
            captureException($e); //Пишем в Sentry необработанное
            return $this->buildErrorResponse(self::INTERNAL, 'Внутренняя ошибка RPC сервера', exception: $e);
        }
    }

    protected function handleSingleRPC(array $payload, Request $request): array
    {
        if (!$this->validateRequest($payload)) {
            return $this->buildErrorResponse(self::INVALID_REQUEST, 'Невалидный RPC запрос');
        }

        $rpcMethodName = $payload['method'];
        $params = $payload['params'] ?? [];
        $id = (string) ($payload['id'] ?? "-1");

        if (!isset($this->methods[$rpcMethodName])) {
            return $this->buildErrorResponse(self::METHOD_NOT_FOUND, 'RPC метод не найден', $id);
        }

        try {
            $this->authUserChecker->checkCpActions($rpcMethodName);
            $this->authUserChecker->checkCpMenu($rpcMethodName);
        } catch (AccessDeniedHttpException $e) {
            return $this->buildErrorResponse($e->getStatusCode(), $e->getMessage(), $id);
        }

        $rpcMethodAttribute = $this->methods[$rpcMethodName];
        $request->attributes->set('is_automapped', $rpcMethodAttribute->isAutomapped);
        $request->attributes->set('_controller', $rpcMethodAttribute->fqn);
        [$controllerName, $methodName] = explode('::', $rpcMethodAttribute->fqn);

        try {
            $controller = $this->getControllerByName($request, $methodName);
        } catch (NotFoundHttpException) {
            return $this->buildErrorResponse(self::METHOD_NOT_FOUND, 'RPC метод не найден', $id);
        } catch (AccessDeniedHttpException $e) {
            return $this->buildErrorResponse($e->getStatusCode(), $e->getMessage(), $id);
        } catch (Throwable $e) {
            captureException($e); //Пишем в Sentry необработанное
            return $this->buildErrorResponse(Response::HTTP_INTERNAL_SERVER_ERROR, 'Внутренняя ошибка сервера', $id, exception: $e);
        }

        $event = new ControllerEvent($this->kernel, $controller, $request, $this->requestType);
        $this->dispatcher->dispatch($event, KernelEvents::CONTROLLER);
        $controller = $event->getController();

        $request->attributes->set('rpc_params', $params);
        $request->attributes->set('validator', $this->validator);

        try {
            $arguments = $this->argumentResolver->getArguments(
                $request,
                $controller,
                new ReflectionMethod($controllerName, $methodName),
            );
        } catch (BadRequestHttpExceptionWithViolations $e) {
            $data = [
                'violations'        => $e->getViolations(),
                'skipSystemMessage' => true,
            ];
            return $this->buildErrorResponse(Response::HTTP_BAD_REQUEST, $e->getMessage(), $id, $data);
        }

        $event = new ControllerArgumentsEvent($this->kernel, $event, $arguments, $request, $this->requestType);
        $this->dispatcher->dispatch($event, KernelEvents::CONTROLLER_ARGUMENTS);
        $controller = $event->getController();
        $arguments = $event->getArguments();

        try {
            $result = $controller(...$arguments);
            return $this->buildSuccessResponse($result, $id);
        } catch (EntityNotFoundDatabaseException $e) {
            return $this->buildErrorResponse(Response::HTTP_NOT_FOUND, $e->getMessage(), $id);
        } catch (HttpException $e) {
            return $this->buildErrorResponse($e->getStatusCode(), $e->getMessage(), $id);
        } catch (DomainException $e) {
            return $this->buildErrorResponse($e->getCode(), $e->getMessage(), $id, $e->getContext());
        } catch (Throwable $e) {
            if ($e instanceof DriverException && OracleErrorHandler::isRaised($e->getCode())) {
                return $this->buildErrorResponse($e->getCode(), OracleErrorHandler::format($e) ?? $e->getMessage(), $id, exception: $e);
            }
            captureException($e); //Пишем необработанное в sentry
            return $this->buildErrorResponse(Response::HTTP_INTERNAL_SERVER_ERROR, 'Внутренняя ошибка сервера', $id, exception: $e);
        }
    }

    private function validateRequest(array $request): bool
    {
        return isset($request['jsonrpc']) && $request['jsonrpc'] === '2.0' && isset($request['method']);
    }

    private function buildSuccessResponse(mixed $result, string $id): array
    {
        return [
            'jsonrpc' => '2.0',
            'result'  => $this->normalizeResult($result),
            'id'      => $id,
        ];
    }

    private function buildErrorResponse(int $code, string $errorMessage, string $id = "-1", array $data = [], ?Throwable $exception = null): array
    {
        $error = [
            'code'    => $code,
            'message' => $errorMessage,
        ];
        if ($data !== []) {
            $error['data'] = $data;
        }
        if ($exception instanceof Throwable && $this->env === 'dev') {
            $error['data']['exception'] = [
                'name'    => $exception::class,
                'message' => $exception->getMessage(),
                'code'    => $exception->getCode(),
                'file'    => $exception->getFile(),
                'line'    => $exception->getLine(),
                'trace'   => $exception->getTraceAsString(),
            ];
        }
        return [
            'jsonrpc' => '2.0',
            'error'   => $error,
            'id'      => $id,
        ];
    }

    private function getControllerByName(Request $request, string $methodName): callable
    {
        $controller = $this->resolver->getController($request);
        if ($controller === false) {
            throw new NotFoundHttpException(
                sprintf('Не удалось найти контроллер для RPC метода "%s"', $methodName)
            );
        }
        return $controller;
    }

    private function normalizeResult(mixed $result): mixed
    {
        $normalizer = (new MapperBuilder())
            ->registerTransformer(function (UnitEnum $enum): array {
                $normalized = [
                    'name' => $enum->name,
                ];
                if ($enum instanceof BackedEnum) {
                    $normalized['value'] = $enum->value;
                }
                if ($enum instanceof Titleable) {
                    $normalized['title'] = $enum->getTitle();
                }
                return $normalized;
            })
            ->normalizer(Format::array());
        return $normalizer->normalize($result);
    }
}
