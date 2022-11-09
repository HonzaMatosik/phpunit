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

use PHPUnit\Framework\TestCase;

class Issue2830Test extends TestCase
{
    /**
     * @dataProvider simpleDataProvider
     */
    public function testMethodUsesDataProvider(): void
    {
        $this->assertTrue(true);
    }

    private function simpleDataProvider()
    {
        return [
            ['foo'],
        ];
    }
}
