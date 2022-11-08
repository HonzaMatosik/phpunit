<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture;

use Generator;
use PHPUnit\Framework\TestCase;

class Issue2380Test extends TestCase
{
    /**
     * @dataProvider generatorData
     */
    public function testGeneratorProvider($data): void
    {
        $this->assertNotEmpty($data);
    }

    /**
     * @return Generator
     */
    private function generatorData()
    {
        yield ['testing'];
    }
}
