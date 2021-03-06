<?php

declare(strict_types=1);

namespace Migrify\ConfigTransformer\FormatSwitcher\PhpParser\NodeFactory;

use Migrify\ConfigTransformer\FormatSwitcher\ValueObject\VariableName;
use PhpParser\BuilderHelpers;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use Symfony\Component\Yaml\Tag\TaggedValue;

final class SingleServicePhpNodeFactory
{
    /**
     * @var CommonNodeFactory
     */
    private $commonNodeFactory;

    /**
     * @var ArgsNodeFactory
     */
    private $argsNodeFactory;

    public function __construct(CommonNodeFactory $commonNodeFactory, ArgsNodeFactory $argsNodeFactory)
    {
        $this->commonNodeFactory = $commonNodeFactory;
        $this->argsNodeFactory = $argsNodeFactory;
    }

    public function createSetService(string $serviceKey): MethodCall
    {
        $classReference = $this->commonNodeFactory->createClassReference($serviceKey);

        return new MethodCall(new Variable(VariableName::SERVICES), 'set', [new Arg($classReference)]);
    }

    public function createCalls(MethodCall $methodCall, array $calls): MethodCall
    {
        foreach ($calls as $call) {

            // @todo can be more items
            $args = [];

            $methodName = $this->resolveCallMethod($call);
            $args[] = new Arg($methodName);

            $argumentsExpr = $this->resolveCallArguments($call);
            $args[] = new Arg($argumentsExpr);

            $returnCloneExpr = $this->resolveCallReturnClone($call);
            if ($returnCloneExpr !== null) {
                $args[] = new Arg($returnCloneExpr);
            }

            $currentArray = current($call);
            if ($currentArray instanceof TaggedValue) {
                $args[] = new Arg(BuilderHelpers::normalizeValue(true));
            }

            $methodCall = new MethodCall($methodCall, 'call', $args);
        }

        return $methodCall;
    }

    private function resolveCallMethod($call): String_
    {
        return new String_($call[0] ?? $call['method'] ?? key($call));
    }

    private function resolveCallArguments($call): Expr
    {
        $arguments = $call[1] ?? $call['arguments'] ?? current($call);
        return $this->argsNodeFactory->resolveExpr($arguments);
    }

    private function resolveCallReturnClone(array $call): ?Expr
    {
        if (isset($call[2]) || isset($call['returns_clone'])) {
            $returnsCloneValue = $call[2] ?? $call['returns_clone'];
            return BuilderHelpers::normalizeValue($returnsCloneValue);
        }

        return null;
    }
}
