<?php

/*
 * This file is part of Composer Upstream Files.
 * (c) 2017 Last Call Media, Rob Bayliss <rob@lastcallmedia.com>
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace LastCall\ComposerUpstreamFiles\Tests;

use Composer\Repository\ArrayRepository;
use LastCall\ComposerUpstreamFiles\FileManager;
use LastCall\ComposerUpstreamFiles\TokenReplacer;
use PHPUnit\Framework\TestCase;

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

    public function testResolvesFilesFromManifest()
    {
        $files = [
      'child.json' => [
        'files' => ['cs' => 'cd'],
      ],
    ];
        $spec = [
      'files' => ['rs' => 'rd'],
      'manifests' => ['child.json'],
    ];
        $this->assertSpecMatches($spec, $files, [
      'rs' => 'rd',
      'cs' => 'cd',
    ]);
    }

    public function testResolvesManifestUri()
    {
        $files = [
      'child' => [
        'files' => ['cs' => 'cd'],
      ],
    ];
        $spec = [
      'tokens' => ['m' => 'child'],
      'files' => ['rs' => 'rd'],
      'manifests' => ['{{m}}'],
    ];
        $this->assertSpecMatches($spec, $files, [
      'rs' => 'rd',
      'cs' => 'cd',
    ]);
    }

    public function testResolvesManifestFilesRelativeToManifest()
    {
        $files = [
      '/foo/child.json' => [
        'files' => ['cs' => 'cd'],
      ],
    ];
        $spec = [
      'files' => ['rs' => 'rd'],
      'manifests' => ['/foo/child.json'],
    ];
        $this->assertSpecMatches($spec, $files, [
      'rs' => 'rd',
      '/foo/cs' => 'cd',
    ]);
    }

    public function testResolvesTokensFromManifest()
    {
        $files = [
      'child.json' => [
        'tokens' => ['child' => 'c'],
        'files' => ['cs-{{root}}-{{child}}' => 'cd-{{root}}-{{child}}'],
      ],
    ];
        $spec = [
      'tokens' => ['root' => 'r'],
      'files' => ['rs-{{root}}' => 'rd-{{root}}'],
      'manifests' => ['child.json'],
    ];
        $this->assertSpecMatches($spec, $files, [
      'rs-r' => 'rd-r',
      'cs-r-c' => 'cd-r-c',
    ]);
    }

    public function testResolvesMultilevelManifests()
    {
        $files = [
      'child1' => [
        'manifests' => ['child2'],
      ],
      'child2' => [
        'files' => ['cs' => 'cd'],
      ],
    ];
        $spec = [
      'manifests' => ['child1'],
    ];
        $this->assertSpecMatches($spec, $files, [
      'cs' => 'cd',
    ]);
    }

    public function testRootTokensOverrideChildTokens()
    {
        $files = [
      'child1' => [
        'manifests' => ['child2'],
      ],
      'child2' => [
        'tokens' => ['token' => 'c'],
        'files' => ['cs{{token}}' => 'cd{{token}}'],
      ],
    ];
        $spec = [
      'tokens' => ['token' => 'r'],
      'manifests' => ['child1'],
    ];
        $this->assertSpecMatches($spec, $files, [
      'csr' => 'cdr',
    ]);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Unknown token token
     */
    public function testChildTokensDoNotLeakToOtherChildren()
    {
        $files = [
      'child' => [
        'tokens' => ['token' => 'c1'],
      ],
      'child2' => [
        'files' => ['{{token}}'],
      ],
    ];
        $spec = [
      'manifests' => ['child', 'child2'],
    ];
        $this->assertSpecMatches($spec, $files, []);
    }

    public function testRemovesSourceExcludes()
    {
        $files = [
      'child' => [
        'files' => [
          '1s' => '1d',
        ],
      ],
    ];
        $spec = [
      'manifests' => ['child'],
      'files' => ['-1s' => ''],
    ];
        $this->assertSpecMatches($spec, $files, []);
    }

    public function testRemovesDestExcludes()
    {
        $files = [
      'child' => [
        'files' => [
          '1s' => '1d',
        ],
      ],
    ];
        $spec = [
      'manifests' => ['child'],
      'files' => ['' => '-1d'],
    ];
        $this->assertSpecMatches($spec, $files, []);
    }

    public function testProcessesChildExcludes()
    {
        $files = [
      'c1' => [
        'files' => ['-cs1' => '', '' => '-cd2'],
        'manifests' => ['c2'],
      ],
      'c2' => [
        'files' => ['cs1' => 'cd1', 'cs2' => 'cd2'],
      ],
    ];
        $spec = [
      'manifests' => ['c1'],
    ];
        $this->assertSpecMatches($spec, $files, []);
    }

    private function assertSpecMatches(array $spec, array $files, $expected)
    {
        $fileContents = [];
        foreach ($files as $filename => $manifestSpec) {
            $fileContents[$filename] = json_encode($manifestSpec);
        }
        $rfs = new RemoteFilesystemMock($fileContents);
        $replacer = new TokenReplacer(new ArrayRepository());
        $manager = new FileManager($replacer, $rfs);

        $this->assertEquals($expected, iterator_to_array($manager->getFiles($spec)));
    }

    private function isAbsolute($path)
    {
        return 0 === strpos($path, '/');
    }
}
