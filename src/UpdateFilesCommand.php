<?php

/*
 * This file is part of Composer Upstream Files.
 * (c) 2017 Last Call Media, Rob Bayliss <rob@lastcallmedia.com>
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace LastCall\ComposerUpstreamFiles;

use Composer\Command\BaseCommand;
use Composer\Factory;
use Composer\IO\ConsoleIO;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateFilesCommand extends BaseCommand
{
    protected function configure()
    {
        $this->setName('upstream-files:update');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $package = $this->getComposer()->getPackage();
        $repository = $this->getComposer()->getRepositoryManager()->getLocalRepository();
        $io = new ConsoleIO($input, $output, $this->getHelperSet());
        $fs = Factory::createRemoteFilesystem($io, $this->getComposer()->getConfig());
        $tokenReplacer = new TokenReplacer($repository);
        $manager = new FileManager($tokenReplacer);

        $extra = $package->getExtra();
        $spec = isset($extra['upstream-files']) ? $extra['upstream-files'] : [];
        assert('is_array($spec)');

        foreach ($manager->getFiles($spec) as $source => $dest) {
            $parts = parse_url($source);
            $io->write(sprintf('Downloading %s', $source));
            $fs->copy(
        sprintf('%s://%s', $parts['scheme'], $parts['host']),
        $source,
        $dest
      );
        }
    }
}
