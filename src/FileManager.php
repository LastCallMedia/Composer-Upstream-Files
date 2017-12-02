<?php

/*
 * This file is part of Composer Upstream Files.
 * (c) 2017 Last Call Media, Rob Bayliss <rob@lastcallmedia.com>
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace LastCall\ComposerUpstreamFiles;

class FileManager
{
    public function __construct(TokenReplacer $replacer)
    {
        $this->replacer = $replacer;
    }

    public function getFiles(array $spec = [])
    {
        $spec += [
      'tokens' => [],
      'files' => [],
    ];
        $files = isset($spec['files']) ? $spec['files'] : [];
        $tokens = isset($spec['tokens']) ? $spec['tokens'] : [];
        assert('is_array($files)');
        assert('is_array($tokens)');

        foreach ($files as $src => $dest) {
            yield $this->replacer->replace($src, $tokens) => $this->replacer->replace($dest, $tokens);
        }
    }
}
