<?php

declare(strict_types=1);

namespace Migrify\ConfigTransformer\FeatureShifter\Tests\Yaml;

use Iterator;
use Migrify\ConfigTransformer\FeatureShifter\ValueObject\ServiceConfig;
use Migrify\ConfigTransformer\FeatureShifter\Yaml\CommonNamespaceResolver;
use PHPUnit\Framework\TestCase;

final class CommonNamespaceResolverTest extends TestCase
{
    /**
     * @var CommonNamespaceResolver
     */
    private $commonNamespaceResolver;

    protected function setUp(): void
    {
        $this->commonNamespaceResolver = new CommonNamespaceResolver();
    }

    /**
     * @param string[] $classes
     * @param string[] $expectedNamespaces
     * @dataProvider provideData()
     */
    public function test(array $classes, int $level, array $expectedNamespaces): void
    {
        $serviceConfig = new ServiceConfig($classes);

        $this->assertSame($expectedNamespaces, $this->commonNamespaceResolver->resolve($serviceConfig, $level));
    }

    public function provideData(): Iterator
    {
        yield [['App\FirstClass', 'App\AnotherClass'], 1, ['App']];
        yield [['App\Wohoo\FirstClass', 'App\Wohoo\AnotherClass'], 2, ['App\Wohoo']];
        yield [['App\Wohoo\FirstClass', 'App\Wohoo\AnotherClass'], 1, ['App']];
        yield [
            [
                'Shopsys\FrameworkBundle\Model\Payment\PaymentRepository',
                'Shopsys\FrameworkBundle\Component\DataFixture\PersistentReferenceFacade',
                'Shopsys\FrameworkBundle\Model\AdvancedSearch\ProductAdvancedSearchConfig',
            ],
            2, ['Shopsys\FrameworkBundle'],
        ];
        yield [
            [
                'Shopsys\FrameworkBundle\Model\Payment\PaymentRepository',
                'Shopsys\FrameworkBundle\Component\DataFixture\PersistentReferenceFacade',
                'Shopsys\FrameworkBundle\Model\AdvancedSearch\ProductAdvancedSearchConfig',
            ],
            3, ['Shopsys\FrameworkBundle\Model', 'Shopsys\FrameworkBundle\Component'],
        ];
    }
}
