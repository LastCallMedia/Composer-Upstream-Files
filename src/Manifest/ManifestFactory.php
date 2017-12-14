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
        $origin = parse_url($uri, PHP_URL_HOST);
        $contents = $this->rfs->getContents($origin, $uri);
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
          'sourceExcludes' => null,
          'destinationExcludes' => null,
        ];
        if (isset($arr['sourceExcludes'])) {
            $arr['sourceExcludes'] = new ExcludeSet($arr['sourceExcludes']);
        }
        if (isset($arr['destinationExcludes'])) {
            $arr['destinationExcludes'] = new ExcludeSet($arr['destinationExcludes']);
        }

        return new Manifest(
          $arr['uri'],
          $arr['files'],
          $arr['tokens'],
          $arr['manifests'],
          $arr['sourceExcludes'],
          $arr['destinationExcludes']
        );
    }
}
