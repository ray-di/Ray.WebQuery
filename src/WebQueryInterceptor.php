<?php

declare(strict_types=1);

namespace Ray\MediaQuery;

use Override;
use Psr\Http\Message\MessageInterface;
use Ray\Aop\MethodInterceptor;
use Ray\Aop\MethodInvocation;
use Ray\MediaQuery\Annotation\Qualifier\WebApiList;
use Ray\MediaQuery\Annotation\WebQuery;
use Ray\MediaQuery\Exception\NotSupportedReturnTypeException;
use ReflectionNamedType;
use ReflectionType;
use ReflectionUnionType;

use function class_exists;
use function is_a;

final class WebQueryInterceptor implements MethodInterceptor
{
    /** @param array<string, array{method: string, path: string}> $webApiList */
    public function __construct(
        private WebApiQueryInterface $webApiQuery,
        private ParamInjectorInterface $paramInjector,
        #[WebApiList]
        private array $webApiList,
        private ReturnEntityInterface $returnEntity,
        private WebResponseMapperInterface $mapper,
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

        // No object mapping requested: return the raw array / string / PSR-7 message.
        if ($webQuery->factory === '' && $entity === null) {
            return $this->rawResponse($returnType, $request, $values);
        }

        /** @var array<mixed> $body */
        $body = $this->webApiQuery->request($request['method'], $request['path'], $values);
        $postFetch = $this->postFetchClass($returnType);
        $isRow = $this->isRow($webQuery, $returnType, $postFetch !== null);

        /** @psalm-suppress MixedAssignment */
        $result = $this->mapper->map($webQuery, $entity, $isRow, $body);

        if ($postFetch !== null) {
            return $postFetch::fromContext(new PostFetchContext($result, $values, $webQuery));
        }

        return $result;
    }

    /**
     * Resolve the return type to a PostFetchInterface class, or null.
     *
     * @return class-string<PostFetchInterface>|null
     */
    private function postFetchClass(ReflectionType|null $returnType): string|null
    {
        if (
            $returnType instanceof ReflectionNamedType
            && class_exists($returnType->getName())
            && is_a($returnType->getName(), PostFetchInterface::class, true)
        ) {
            /** @var class-string<PostFetchInterface> */
            return $returnType->getName();
        }

        return null;
    }

    /**
     * A single object is returned for `type: 'row'`, a union return type, or a
     * named (non-array, non-PostFetch) class return type. Otherwise a list.
     */
    private function isRow(WebQuery $webQuery, ReflectionType|null $returnType, bool $isPostFetch): bool
    {
        if ($webQuery->type === 'row') {
            return true;
        }

        if ($isPostFetch) {
            return false;
        }

        if ($returnType instanceof ReflectionUnionType) {
            return true;
        }

        return $returnType instanceof ReflectionNamedType && $returnType->getName() !== 'array';
    }

    /**
     * Return the raw response in the form requested by the return type:
     * array / string / MessageInterface.
     *
     * @param array{method: string, path: string} $request
     * @param array<string, string>               $values
     *
     * @return array<mixed>|string|MessageInterface
     */
    private function rawResponse(
        ReflectionType|null $returnType,
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
