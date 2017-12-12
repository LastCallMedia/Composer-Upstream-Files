<?php

/*
 * This file is part of Composer Upstream Files.
 * (c) 2017 Last Call Media, Rob Bayliss <rob@lastcallmedia.com>
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace LastCall\ComposerUpstreamFiles;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\Capability\CommandProvider;
use Composer\Plugin\Capable;
use Composer\Plugin\PluginInterface;
use LastCall\ComposerUpstreamFiles\Command\ListFilesCommand;
use LastCall\ComposerUpstreamFiles\Command\UpdateFilesCommand;

class Plugin implements PluginInterface, Capable, CommandProvider
{
    public function activate(Composer $composer, IOInterface $io)
    {
        // No-op.
    }

    public function getCapabilities()
    {
        return [
          CommandProvider::class => static::class,
        ];
    }

    public function getCommands()
    {
        return [
          new UpdateFilesCommand(),
          new ListFilesCommand(),
        ];
    }
}
