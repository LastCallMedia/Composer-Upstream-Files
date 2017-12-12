<?php

/*
 * This file is part of Composer Upstream Files.
 * (c) 2017 Last Call Media, Rob Bayliss <rob@lastcallmedia.com>
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace LastCall\ComposerUpstreamFiles\Command;

use Composer\Command\BaseCommand;
use Composer\Factory;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use LastCall\ComposerUpstreamFiles\Manifest\ManifestFactory;
use LastCall\ComposerUpstreamFiles\TokenReplacer;
use LastCall\ComposerUpstreamFiles\FileManager;
use Composer\Util\Filesystem;

class UpdateFilesCommand extends BaseCommand
{
    protected function configure()
    {
        $this->setName('upstream-files:update');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $composer = $this->getComposer(true);
        $package = $composer->getPackage();
        $repository = $composer->getRepositoryManager()->getLocalRepository();
        $io = $this->getIO();
        $rfs = Factory::createRemoteFilesystem($io, $this->getComposer()->getConfig());
        $fs = new Filesystem();

        $manifestFactory = new ManifestFactory($rfs);
        $tokenReplacer = new TokenReplacer($repository);
        $manager = new FileManager($tokenReplacer, $manifestFactory);
        $manifest = $manifestFactory->fromPackage($package);

        foreach ($manager->getFiles($manifest) as $source => $dest) {
            $parts = parse_url($source);
            $host = sprintf('%s://%s', $parts['scheme'], $parts['host']);
            $io->write(sprintf('Downloading %s', $source));
            $fs->ensureDirectoryExists(dirname($dest));
            $rfs->copy($host, $source, $dest);
        }
    }
}
