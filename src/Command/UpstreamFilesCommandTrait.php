<?php

/*
 * This file is part of Composer Upstream Files.
 * (c) 2017 Last Call Media, Rob Bayliss <rob@lastcallmedia.com>
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace LastCall\ComposerUpstreamFiles\Command;

use Composer\Util\RemoteFilesystem;
use LastCall\ComposerUpstreamFiles\FileManager;
use LastCall\ComposerUpstreamFiles\ManifestFactory;
use LastCall\ComposerUpstreamFiles\TokenReplacer;

trait UpstreamFilesCommandTrait
{
    protected function getFileManager(RemoteFilesystem $rfs)
    {
        $repository = $this->getComposer()->getRepositoryManager()->getLocalRepository();
        $manifestFactory = new ManifestFactory($rfs);
        $tokenReplacer = new TokenReplacer($repository);

        return new FileManager($tokenReplacer, $manifestFactory);
    }
}
