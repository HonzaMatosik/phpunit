<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\Code;

use function sprintf;
use PHPUnit\Event\TestData\DataFromDataProvider;
use PHPUnit\Event\TestData\TestDataCollection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use PHPUnit\Metadata\MetadataCollection;

#[CoversClass(TestMethod::class)]
final class TestMethodTest extends TestCase
{
    public function testConstructorSetsValues(): void
    {
        $className            = 'FooTest';
        $methodName           = 'testBar';
        $file                 = 'FooTest.php';
        $line                 = 1;
        $prettifiedClassName  = 'Foo';
        $prettifiedMethodName = 'Bar';
        $testData             = TestDataCollection::fromArray([]);
        $metadata             = MetadataCollection::fromArray([]);

        $test = new TestMethod(
            $className,
            $methodName,
            $file,
            $line,
            $prettifiedClassName,
            $prettifiedMethodName,
            $metadata,
            $testData
        );

        $this->assertSame($className, $test->className());
        $this->assertSame($methodName, $test->methodName());
        $this->assertSame($file, $test->file());
        $this->assertSame($line, $test->line());
        $this->assertSame($metadata, $test->metadata());
        $this->assertSame($testData, $test->testData());
    }

    public function testNameReturnsNameWhenTestDoesNotHaveDataFromDataProvider(): void
    {
        $test = new TestMethod(
            'FooTest',
            'testBar',
            'FooTest.php',
            1,
            'Foo',
            'Bar',
            MetadataCollection::fromArray([]),
            TestDataCollection::fromArray([])
        );

        $this->assertSame($test->methodName(), $test->name());
    }

    public function testNameReturnsNameWhenTestHasDataFromDataProviderAndDataSetNameIsInt(): void
    {
        $dataSetName = 9000;

        $test = new TestMethod(
            'FooTest',
            'testBar',
            'FooTest.php',
            1,
            'Foo',
            'Bar',
            MetadataCollection::fromArray([]),
            TestDataCollection::fromArray([
                DataFromDataProvider::from(
                    $dataSetName,
                    'data'
                ),
            ])
        );

        $expected = sprintf(
            '%s with data set #%d',
            $test->methodName(),
            $dataSetName
        );

        $this->assertSame($expected, $test->name());
    }

    public function testNameReturnsNameWhenTestHasDataFromDataProviderAndDataSetNameIsString(): void
    {
        $dataSetName = 'bar-9000';

        $test = new TestMethod(
            'FooTest',
            'testBar',
            'FooTest.php',
            1,
            'Foo',
            'Bar',
            MetadataCollection::fromArray([]),
            TestDataCollection::fromArray([
                DataFromDataProvider::from(
                    $dataSetName,
                    'data'
                ),
            ])
        );

        $expected = sprintf(
            '%s with data set "%s"',
            $test->methodName(),
            $dataSetName
        );

        $this->assertSame($expected, $test->name());
    }
}
