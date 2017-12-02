<?php

/*
 * This file is part of Composer Upstream Files.
 * (c) 2017 Last Call Media, Rob Bayliss <rob@lastcallmedia.com>
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace LastCall\ComposerUpstreamFiles\Tests;

use Composer\Package\Package;
use Composer\Repository\ArrayRepository;
use PHPUnit\Framework\TestCase;
use LastCall\ComposerUpstreamFiles\TokenReplacer;

class TokenReplacerTest extends TestCase
{
    public function getReplacementTests()
    {
        return [
      ['foo{{foo}}', 'foobar'],
      ['foo{{foo}}{{foo}}', 'foobarbar'],
      ['foo{{foo}}{{baz}}', 'foobarboo'],
    ];
    }

    /**
     * @dataProvider getReplacementTests
     */
    public function testSimpleReplacements($input, $expected)
    {
        $replacer = new TokenReplacer(new ArrayRepository());
        $this->assertEquals($expected, $replacer->replace($input, [
      'foo' => 'bar',
      'baz' => 'boo',
    ]));
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Unknown token bar
     */
    public function testInvalidToken()
    {
        $replacer = new TokenReplacer(new ArrayRepository());
        $replacer->replace('foo{{bar}}', []);
    }

    public function getRepositoryTokenTests()
    {
        return [
      ['foo{{foo.version}}', 'foo1.0.0'],
    ];
    }

    /**
     * @dataProvider getRepositoryTokenTests
     */
    public function testRepositoryTokens($input, $expected)
    {
        $repository = new ArrayRepository([
      new Package('foo', '1.0.0', '1.0.0'),
    ]);
        $replacer = new TokenReplacer($repository);
        $this->assertEquals($expected, $replacer->replace($input, []));
    }

    public function getNestedTokenTests()
    {
        return [
      ['foo{{bar}}', ['bar' => '{{baz}}', 'baz' => 'boo'], 'fooboo'],
      ['foo{{bar}}', ['bar' => '{{foo.version}}'], 'foo1.0.0'],
    ];
    }

    /**
     * @dataProvider getNestedTokenTests
     */
    public function testNestedTokens($input, $tokens, $expected)
    {
        $repository = new ArrayRepository([
      new Package('foo', '1.0.0', '1.0.0'),
    ]);
        $replacer = new TokenReplacer($repository);
        $this->assertEquals($expected, $replacer->replace($input, $tokens));
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Infinite loop detected in foo token
     */
    public function testInfiniteLoopError()
    {
        $replacer = new TokenReplacer(new ArrayRepository());
        $replacer->replace('{{foo}}', [
      'foo' => '{{foo}}',
    ]);
    }
}
