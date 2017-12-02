<?php

/*
 * This file is part of Composer Upstream Files.
 * (c) 2017 Last Call Media, Rob Bayliss <rob@lastcallmedia.com>
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace LastCall\ComposerUpstreamFiles\Tests;

use Composer\Repository\ArrayRepository;
use PHPUnit\Framework\TestCase;
use LastCall\ComposerUpstreamFiles\TokenReplacer;
use LastCall\ComposerUpstreamFiles\FileManager;

class FileManagerTest extends TestCase
{
    public function getTestCases()
    {
        return [
      [[], []],
      [
        ['files' => ['foo' => 'bar']],
        ['foo' => 'bar'],
      ],
      [
        [
          'tokens' => ['t1' => 'r1'],
          'files' => ['s1{{t1}}' => 'd1{{t1}}'],
        ],
        ['s1r1' => 'd1r1'],
      ],
    ];
    }

    /**
     * @dataProvider getTestCases
     */
    public function testFileManager(array $spec, array $expectedFiles)
    {
        $replacer = new TokenReplacer(new ArrayRepository());
        $manager = new FileManager($replacer);
        $files = $manager->getFiles($spec);
        $this->assertEquals($expectedFiles, iterator_to_array($files));
    }
}
