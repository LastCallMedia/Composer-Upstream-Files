<?php

/*
 * This file is part of Composer Upstream Files.
 * (c) 2017 Last Call Media, Rob Bayliss <rob@lastcallmedia.com>
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace LastCall\ComposerUpstreamFiles\Tests;

use Composer\Repository\ArrayRepository;
use LastCall\ComposerUpstreamFiles\ExcludeSet;
use LastCall\ComposerUpstreamFiles\FileManager;
use LastCall\ComposerUpstreamFiles\Manifest\Manifest;
use LastCall\ComposerUpstreamFiles\Manifest\ManifestFactory;
use LastCall\ComposerUpstreamFiles\TokenReplacer;
use PHPUnit\Framework\TestCase;

class FileManagerTest extends TestCase
{

    public function getTestCases()
    {
        return [
          [new Manifest(''), []],
          [new Manifest('', ['foo' => 'bar']), ['foo' => 'bar']],
          [
            new Manifest('', ['s1{{t1}}' => 'd1{{t1}}'], ['t1' => 'r1']),
            ['s1r1' => 'd1r1'],
          ],
        ];
    }

    /**
     * @dataProvider getTestCases
     */
    public function testFileManager(Manifest $manifest, array $expectedFiles)
    {
        $replacer = new TokenReplacer(new ArrayRepository());
        $factory = new ManifestFactory(new RemoteFilesystemMock());
        $manager = new FileManager($replacer, $factory);
        $files = $manager->getFiles($manifest);
        $this->assertEquals($expectedFiles, iterator_to_array($files));
    }

    public function testResolvesFilesFromManifest()
    {
        $files = [
          'child' => [
            'files' => ['cs' => 'cd'],
          ],
        ];
        $manifest = new Manifest(
          '',
          ['rs' => 'rd'],
          [],
          ['child']
        );
        $this->assertSpecMatches(
          $manifest,
          $files,
          [
            'rs' => 'rd',
            'cs' => 'cd',
          ]
        );
    }

    public function testResolvesManifestUri()
    {
        $files = [
          'child' => [
            'files' => ['cs' => 'cd'],
          ],
        ];
        $manifest = new Manifest(
          '',
          [],
          ['m' => 'child'],
          ['{{m}}']
        );
        $this->assertSpecMatches(
          $manifest,
          $files,
          [
            'cs' => 'cd',
          ]
        );
    }

    public function testResolvesManifestFilesRelativeToManifest()
    {
        $files = [
          '/foo/child' => [
            'files' => ['cs' => 'cd'],
          ],
        ];
        $manifest = new Manifest(
          '',
          [],
          [],
          ['/foo/child']
        );
        $this->assertSpecMatches(
          $manifest,
          $files,
          [
            '/foo/cs' => 'cd',
          ]
        );
    }

    public function testResolvesTokensFromManifest()
    {
        $files = [
          'child' => [
            'tokens' => ['child' => 'c'],
            'files' => ['cs-{{root}}-{{child}}' => 'cd-{{root}}-{{child}}'],
          ],
        ];
        $manifest = new Manifest(
          '',
          [],
          ['root' => 'r'],
          ['child']
        );
        $this->assertSpecMatches(
          $manifest,
          $files,
          [
            'cs-r-c' => 'cd-r-c',
          ]
        );
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
        $manifest = new Manifest(
          '',
          [],
          [],
          ['child1']
        );
        $this->assertSpecMatches(
          $manifest,
          $files,
          [
            'cs' => 'cd',
          ]
        );
    }

    public function testRootTokensOverrideChildTokens()
    {
        $files = [
          'child' => [
            'tokens' => ['token' => 'c'],
            'files' => ['cs{{token}}' => 'cd{{token}}'],
          ],
        ];
        $manifest = new Manifest(
          '',
          [],
          ['token' => 'r'],
          ['child']
        );
        $this->assertSpecMatches(
          $manifest,
          $files,
          [
            'csr' => 'cdr',
          ]
        );
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
        $manifest = new Manifest(
          '',
          [],
          [],
          ['child', 'child2']
        );
        $this->assertSpecMatches($manifest, $files, []);
    }

    public function testRemovesExcludesFromSelf() {
        $manifest = new Manifest(
          '',
          ['1' => 'one', '2' => 'two', '3' => 'three'],
          [],
          [],
          new ExcludeSet(['/1/']),
          new ExcludeSet(['/three/'])
        );
        $this->assertSpecMatches($manifest, [], ['2' => 'two']);
    }

    public function testRemovesExcludesFromReferenced() {
        $files = [
          'child' => [
            'srcExcludes' => ['/1/'],
            'destExcludes' => ['/two/'],
            'files' => [
              '1' => 'one',
              '2' => 'two',
              '3' => 'three',
              '4' => 'four',
              '5' => 'five',
            ],
          ],
        ];
        $manifest = new Manifest(
          '',
          [],
          [],
          ['child'],
          new ExcludeSet(['/3/']),
          new ExcludeSet(['/four/'])
        );
        $this->assertSpecMatches($manifest, $files, ['5' => 'five']);
    }

    private function assertSpecMatches(Manifest $root, array $files, $expected)
    {
        $fileContents = [];
        foreach ($files as $filename => $manifestSpec) {
            $fileContents[$filename] = json_encode($manifestSpec);
        }
        $rfs = new RemoteFilesystemMock($fileContents);
        $factory = new ManifestFactory($rfs);
        $replacer = new TokenReplacer(new ArrayRepository());
        $manager = new FileManager($replacer, $factory);
        $this->assertEquals(
          $expected,
          iterator_to_array($manager->getFiles($root))
        );
    }

}
