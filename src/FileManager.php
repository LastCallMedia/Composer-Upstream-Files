<?php

/*
 * This file is part of Composer Upstream Files.
 * (c) 2017 Last Call Media, Rob Bayliss <rob@lastcallmedia.com>
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace LastCall\ComposerUpstreamFiles;

use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Psr7\UriResolver;
use LastCall\ComposerUpstreamFiles\Manifest\ManifestFactory;
use LastCall\ComposerUpstreamFiles\Manifest\Manifest;

class FileManager
{

    public function __construct(
      TokenReplacer $replacer,
      ManifestFactory $factory
    ) {
        $this->replacer = $replacer;
        $this->factory = $factory;
    }

    public function getFiles(Manifest $manifest, Manifest $parent = NULL)
    {
        $tokens = $parent ? $parent->getTokens() + $manifest->getTokens() : $manifest->getTokens();
        $srcExclude = $manifest->getSourceExcludes();
        $destExclude = $manifest->getDestExcludes();

        foreach ($manifest->getFiles() as $src => $dest) {
            $src = $this->resolveUri(
              $this->replacer->replace($src, $tokens),
              $manifest->getUri()
            );
            $dest = $this->replacer->replace($dest, $tokens);

            // Filter files against the exclude patterns defined in this file.
            // They'll be filtered against the parent as well as they bubble up.
            if(!$srcExclude->matches($src) && !$destExclude->matches($dest)) {
                yield $src => $dest;
            }
        }

        foreach ($manifest->getManifests() as $ref) {
            $refUri = $this->resolveUri(
              $this->replacer->replace($ref, $tokens),
              $manifest->getUri()
            );
            $refManifest = $this->factory->fromRemoteFile($refUri);
            $files = $this->getFiles($refManifest, $manifest);
            foreach($files as $src => $dest) {
                if(!$srcExclude->matches($src) && !$destExclude->matches($dest)) {
                    yield $src => $dest;
                }
            }
        }
    }

    private function resolveUri($uri, $parent)
    {
        return (string)UriResolver::resolve(
          new Uri($parent),
          new Uri($uri)
        );
    }
}
