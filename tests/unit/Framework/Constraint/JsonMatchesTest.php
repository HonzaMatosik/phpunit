<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\Constraint;

use function json_encode;
use function sprintf;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Util\Json;
use PHPUnit\Util\ThrowableToStringMapper;

#[CoversClass(JsonMatches::class)]
#[Small]
final class JsonMatchesTest extends ConstraintTestCase
{
    #[DataProvider('evaluateDataprovider')]
    public function testEvaluate(bool $expected, string $jsonOther, string $jsonValue): void
    {
        $constraint = new JsonMatches($jsonValue);

        $this->assertEquals($expected, $constraint->evaluate($jsonOther, '', true));
    }

    #[DataProvider('evaluateThrowsExpectationFailedExceptionWhenJsonIsValidButDoesNotMatchDataprovider')]
    public function testEvaluateThrowsExpectationFailedExceptionWhenJsonIsValidButDoesNotMatch(string $jsonOther, string $jsonValue): void
    {
        $constraint = new JsonMatches($jsonValue);

        try {
            $constraint->evaluate($jsonOther, '', false);
            $this->fail(sprintf('Expected %s to be thrown.', ExpectationFailedException::class));
        } catch (ExpectationFailedException $expectedException) {
            $comparisonFailure = $expectedException->getComparisonFailure();
            $this->assertNotNull($comparisonFailure);

            [$error, $jsonOtherCanonicalized] = Json::canonicalize($jsonOther);
            [$error, $jsonValueCanonicalized] = Json::canonicalize($jsonValue);

            $this->assertSame(Json::prettify($jsonOtherCanonicalized), $comparisonFailure->getActualAsString());
            $this->assertSame(Json::prettify($jsonValueCanonicalized), $comparisonFailure->getExpectedAsString());
            $this->assertSame('Failed asserting that two json values are equal.', $comparisonFailure->getMessage());
        }
    }

    public function testToString(): void
    {
        $jsonValue  = json_encode(['Mascott' => 'Tux']);
        $constraint = new JsonMatches($jsonValue);

        $this->assertEquals('matches JSON string "' . $jsonValue . '"', $constraint->toString());
    }

    public function testFailErrorWithInvalidValueAndOther(): void
    {
        $constraint = new JsonMatches('{"Mascott"::}');

        try {
            $constraint->evaluate('{"Mascott"::}', '', false);
            $this->fail(sprintf('Expected %s to be thrown.', ExpectationFailedException::class));
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<'EOF'
Failed asserting that '{"Mascott"::}' matches JSON string "{"Mascott"::}".

EOF
                ,
                ThrowableToStringMapper::map($e)
            );
        }
    }

    public function testFailErrorWithValidValueAndInvalidOther(): void
    {
        $constraint = new JsonMatches('{"Mascott"::}');

        try {
            $constraint->evaluate('{"Mascott":"Tux"}', '', false);
            $this->fail(sprintf('Expected %s to be thrown.', ExpectationFailedException::class));
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<'EOF'
Failed asserting that '{"Mascott":"Tux"}' matches JSON string "{"Mascott"::}".

EOF
                ,
                ThrowableToStringMapper::map($e)
            );
        }
    }

    public function testEmptyObjectNotConvertedToArrayInDiff(): void
    {
        $constraint = new JsonMatches('{"obj": {}, "val": 1}');

        try {
            $constraint->evaluate('{"obj": {}, "val": 2}', '', false);
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<'EOF'
Failed asserting that '{"obj": {}, "val": 2}' matches JSON string "{"obj": {}, "val": 1}".
--- Expected
+++ Actual
@@ @@
 {
     "obj": {},
-    "val": 1
+    "val": 2
 }

EOF
                ,
                ThrowableToStringMapper::map($e)
            );
        }
    }

    public function testObjectAreCanonicalizedInDiff(): void
    {
        $constraint = new JsonMatches('{"obj": {"x": 1, "y": 2}, "val": 1}');

        try {
            $constraint->evaluate('{"obj": {"y": 2, "x": 1}, "val": 2}', '', false);
        } catch (ExpectationFailedException $e) {
            $this->assertEquals(
                <<<'EOF'
Failed asserting that '{"obj": {"y": 2, "x": 1}, "val": 2}' matches JSON string "{"obj": {"x": 1, "y": 2}, "val": 1}".
--- Expected
+++ Actual
@@ @@
         "x": 1,
         "y": 2
     },
-    "val": 1
+    "val": 2
 }

EOF
                ,
                ThrowableToStringMapper::map($e)
            );
        }
    }

    private static function evaluateDataprovider(): array
    {
        return [
            'valid JSON'                              => [true, json_encode(['Mascott' => 'Tux']), json_encode(['Mascott' => 'Tux'])],
            'error syntax'                            => [false, '{"Mascott"::}', json_encode(['Mascott' => 'Tux'])],
            'error UTF-8'                             => [false, json_encode('\xB1\x31'), json_encode(['Mascott' => 'Tux'])],
            'invalid JSON in class instantiation'     => [false, json_encode(['Mascott' => 'Tux']), '{"Mascott"::}'],
            'string type not equals number'           => [false, '{"age": "5"}', '{"age": 5}'],
            'string type not equals boolean'          => [false, '{"age": "true"}', '{"age": true}'],
            'string type not equals null'             => [false, '{"age": "null"}', '{"age": null}'],
            'object fields are unordered'             => [true, '{"first":1, "second":"2"}', '{"second":"2", "first":1}'],
            'child object fields are unordered'       => [true, '{"Mascott": {"name":"Tux", "age":5}}', '{"Mascott": {"age":5, "name":"Tux"}}'],
            'null field different from missing field' => [false, '{"present": true, "missing": null}', '{"present": true}'],
            'array elements are ordered'              => [false, '["first", "second"]', '["second", "first"]'],
            'single boolean valid json'               => [true, 'true', 'true'],
            'single number valid json'                => [true, '5.3', '5.3'],
            'single null valid json'                  => [true, 'null', 'null'],
            'objects are not arrays'                  => [false, '{}', '[]'],
        ];
    }

    private static function evaluateThrowsExpectationFailedExceptionWhenJsonIsValidButDoesNotMatchDataprovider(): array
    {
        return [
            'error UTF-8'                             => [json_encode('\xB1\x31'), json_encode(['Mascott' => 'Tux'])],
            'string type not equals number'           => ['{"age": "5"}', '{"age": 5}'],
            'string type not equals boolean'          => ['{"age": "true"}', '{"age": true}'],
            'string type not equals null'             => ['{"age": "null"}', '{"age": null}'],
            'null field different from missing field' => ['{"missing": null, "present": true}', '{"present": true}'],
            'array elements are ordered'              => ['["first", "second"]', '["second", "first"]'],
        ];
    }
}
