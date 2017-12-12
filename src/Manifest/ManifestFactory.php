<?php

/*
 * This file is part of Composer Upstream Files.
 * (c) 2017 Last Call Media, Rob Bayliss <rob@lastcallmedia.com>
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace LastCall\ComposerUpstreamFiles\Manifest;

use Composer\Package\PackageInterface;
use Composer\Util\RemoteFilesystem;
use LastCall\ComposerUpstreamFiles\ExcludeSet;
use Seld\JsonLint\JsonParser;

class ManifestFactory
{
    public function __construct(RemoteFilesystem $rfs)
    {
        $this->rfs = $rfs;
        $this->parser = new JsonParser();
    }

    public function fromRemoteFile($uri)
    {
        $contents = $this->rfs->getContents($this->parseOrigin($uri), $uri);
        $obj = $this->parser->parse($contents, JsonParser::PARSE_TO_ASSOC);
        $obj['uri'] = $uri;

        return $this->fromArray($obj);
    }

    public function fromPackage(PackageInterface $package)
    {
        $extra = $package->getExtra();
        $extra += ['upstream-files' => []];

        return $this->fromArray($extra['upstream-files']);
    }

    public function fromArray(array $arr)
    {
        $arr += [
          'uri' => '',
          'files' => [],
          'tokens' => [],
          'manifests' => [],
          'srcExcludes' => null,
          'destExcludes' => null,
        ];
        if (isset($arr['srcExcludes'])) {
            $arr['srcExcludes'] = new ExcludeSet($arr['srcExcludes']);
        }
        if (isset($arr['destExcludes'])) {
            $arr['destExcludes'] = new ExcludeSet($arr['destExcludes']);
        }

        return new Manifest(
          $arr['uri'],
          $arr['files'],
          $arr['tokens'],
          $arr['manifests'],
          $arr['srcExcludes'],
          $arr['destExcludes']
        );
    }

    private function parseOrigin($url)
    {
        $parts = parse_url($url);

        return sprintf('%s://%s', $parts['scheme'], $parts['host']);
    }
}
