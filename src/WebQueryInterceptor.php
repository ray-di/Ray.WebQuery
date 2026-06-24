<?php

declare(strict_types=1);

namespace Ray\MediaQuery;

use Override;
use Psr\Http\Message\MessageInterface;
use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;
use Ray\Di\InjectorInterface;
use Ray\MediaQuery\Annotation\Qualifier\WebApiList;
use Ray\MediaQuery\Annotation\WebQuery;
use Ray\MediaQuery\Exception\NotSupportedReturnTypeException;
use ReflectionNamedType;
use ReflectionUnionType;

use function array_is_list;
use function class_exists;
use function is_a;
use function is_array;

final class WebQueryInterceptor implements MethodInterceptor
{
    /** @param array<string, array{method: string, path: string}> $webApiList */
    public function __construct(
        private WebApiQueryInterface $webApiQuery,
        private ParamInjectorInterface $paramInjector,
        #[WebApiList]
        private array $webApiList,
        private ReturnEntityInterface $returnEntity,
        private WebFetchFactoryInterface $webFetchFactory,
        private InjectorInterface $injector,
    ) {
    }

    #[Override]
    public function invoke(MethodInvocation $invocation): mixed
    {
        $method = $invocation->getMethod();
        /** @var WebQuery $webQuery */
        $webQuery = $method->getAnnotation(WebQuery::class);
        /** @var array<string, string> $values */
        $values = $this->paramInjector->getArguments($invocation);
        $request = $this->webApiList[$webQuery->id];
        $returnType = $method->getReturnType();
        $entity = ($this->returnEntity)($method);

        $resolvedType = $returnType instanceof ReflectionUnionType
            ? $returnType
            : ($returnType instanceof ReflectionNamedType ? $returnType : null);
        $fetch = $this->webFetchFactory->factory($webQuery, $entity, $resolvedType);

        if ($fetch instanceof WebFetchAssoc) {
            return $this->invokeLegacy($returnType, $request, $values);
        }

        /** @var array<mixed> $body */
        $body = $this->webApiQuery->request($request['method'], $request['path'], $values);

        $isPostFetch = $returnType instanceof ReflectionNamedType
            && class_exists($returnType->getName())
            && is_a($returnType->getName(), PostFetchInterface::class, true);

        $isRow = $webQuery->type === 'row'
            || (! $isPostFetch && (
                $returnType instanceof ReflectionUnionType
                || ($returnType instanceof ReflectionNamedType && $returnType->getName() !== 'array')
            ));

        /** @psalm-suppress MixedAssignment */
        $result = $isRow
            ? $this->doFetchRow($body, $fetch)
            : $this->doFetchAll($body, $fetch);

        if ($returnType instanceof ReflectionNamedType) {
            $typeName = $returnType->getName();
            if (class_exists($typeName) && is_a($typeName, PostFetchInterface::class, true)) {
                $context = new PostFetchContext($result, $values, $webQuery);

                return $typeName::fromContext($context);
            }
        }

        return $result;
    }

    /**
     * Handle a row (single object) fetch.
     *
     * Accepts either a top-level associative array (single object) or a list
     * whose first element is used.
     *
     * @param array<mixed> $body
     */
    private function doFetchRow(array $body, WebFetchInterface $fetch): mixed
    {
        if ($body === []) {
            return null;
        }

        if (array_is_list($body)) {
            if (! isset($body[0])) {
                return null;
            }

            /** @var array<string, mixed> $first */
            $first = $body[0];

            return $fetch->fetchRow($first, $this->injector);
        }

        /** @var array<string, mixed> $body */
        return $fetch->fetchRow($body, $this->injector);
    }

    /**
     * Handle a row_list (multiple objects) fetch.
     *
     * When the top-level JSON is a single object (not a list), it is wrapped in
     * a one-element array so callers always receive a list.
     *
     * @param array<mixed>              $body
     * @return array<mixed>
     */
    private function doFetchAll(array $body, WebFetchInterface $fetch): array
    {
        if ($body === []) {
            return [];
        }

        if (! array_is_list($body)) {
            /** @var array<string, mixed> $body */
            return [$fetch->fetchRow($body, $this->injector)];
        }

        /** @var array<int, array<string, mixed>> $body */
        return $fetch->fetchAll($body, $this->injector);
    }

    /**
     * Legacy path: array / string / MessageInterface — no changes from original.
     *
     * @param array{method: string, path: string} $request
     * @param array<string, string>               $values
     * @return array<mixed>|string|MessageInterface
     */
    private function invokeLegacy(
        mixed $returnType,
        array $request,
        array $values,
    ): array|string|MessageInterface {
        if (
            $returnType instanceof ReflectionNamedType &&
            is_a($returnType->getName(), MessageInterface::class, true)
        ) {
            return $this->webApiQuery->getHttpMessage($request['method'], $request['path'], $values);
        }

        if ($returnType instanceof ReflectionNamedType && $returnType->getName() === 'string') {
            return $this->webApiQuery->getStringBody($request['method'], $request['path'], $values);
        }

        if ($returnType instanceof ReflectionNamedType && $returnType->getName() === 'array') {
            return $this->webApiQuery->request($request['method'], $request['path'], $values);
        }

        throw new NotSupportedReturnTypeException();
    }
}
