<?php

/*
 * This file is part of Composer Upstream Files.
 * (c) 2017 Last Call Media, Rob Bayliss <rob@lastcallmedia.com>
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace LastCall\ComposerUpstreamFiles;

use Composer\Util\RemoteFilesystem;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Psr7\UriResolver;
use Seld\JsonLint\JsonParser;

class FileManager
{
    public function __construct(TokenReplacer $replacer, RemoteFilesystem $filesystem = null)
    {
        $this->replacer = $replacer;
        $this->parser = new JsonParser();
        $this->filesystem = $filesystem;
    }

    public function getFiles(array $spec = [])
    {
        return $this->doGetFiles($spec);
    }

    private function doGetFiles(array $spec = [], $parentUri = './composer.json')
    {
        $spec += [
      'tokens' => [],
      'files' => [],
      'manifests' => [],
    ];
        $specFiles = isset($spec['files']) ? $spec['files'] : [];
        $tokens = isset($spec['tokens']) ? $spec['tokens'] : [];
        $manifests = isset($spec['manifests']) ? $spec['manifests'] : [];
        assert('is_array($specFiles)');
        assert('is_array($tokens)');
        assert('is_array($manifests)');

        $files = [];

        foreach ($specFiles as $src => $dest) {
            $src = $this->resolveUri($this->replacer->replace($src, $tokens), $parentUri);
            $dest = $this->replacer->replace($dest, $tokens);
            $files[$src] = $dest;
        }

        foreach ($manifests as $manifest) {
            $manifestUri = $this->resolveUri($this->replacer->replace($manifest, $tokens), $parentUri);
            foreach ($this->filesFromManifest($manifestUri, $tokens) as $src => $dest) {
                $files[$src] = $dest;
            }
        }

        $files = $this->filterNegations($files);

        // Returning an iterator for right now to reserve the right to switch to
        // a generator in the future.
        return new \ArrayIterator($files);
    }

    private function filesFromManifest($manifest, $tokens)
    {
        $originUrl = $this->parseOrigin($manifest);

        $manifestContents = $this->filesystem->getContents($originUrl, $manifest);
        $parsed = (new JsonParser())->parse($manifestContents, JsonParser::PARSE_TO_ASSOC);

        $values['tokens'] = $tokens;
        $spec = [
      'files' => isset($parsed['files']) ? $parsed['files'] : [],
      'manifests' => isset($parsed['manifests']) ? $parsed['manifests'] : [],
    ];
        if (isset($parsed['tokens'])) {
            $spec['tokens'] = $tokens + $parsed['tokens'];
        } else {
            $spec['tokens'] = $tokens;
        }

        return $this->doGetFiles($spec, $manifest);
    }

    private function filterNegations(array $files)
    {
        // Contains a keyed array of all negative sources in the form:
        // ['-src1' => 1]
        $negativeSources = array_flip(array_filter(array_keys($files), function ($src) {
            return 0 === strpos($src, '-');
        }));
        // Contains a keyed array of all negative destinations in the form:
        // ['-dest1' => 1]
        $negativeDestinations = array_flip(array_filter($files, function ($dest) {
            return 0 === strpos($dest, '-');
        }));

        return array_filter($files, function ($dest, $src) use ($negativeSources, $negativeDestinations) {
            return !isset($negativeSources[$src])
        && !isset($negativeSources["-$src"])
        && !isset($negativeDestinations[$dest])
        && !isset($negativeDestinations["-$dest"]);
        }, ARRAY_FILTER_USE_BOTH);
    }

    private function parseOrigin($url)
    {
        $parts = parse_url($url);

        return sprintf('%s://%s', $parts['scheme'], $parts['host']);
    }

    private function resolveUri($uri, $parent)
    {
        return (string) UriResolver::resolve(
      new Uri($parent),
      new Uri($uri)
    );
    }
}
