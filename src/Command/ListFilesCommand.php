<?php
/**
 * Created by PhpStorm.
 * User: rbayliss
 * Date: 12/11/17
 * Time: 6:33 PM
 */

namespace LastCall\ComposerUpstreamFiles\Command;


use Composer\Command\BaseCommand;
use Composer\Factory;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use LastCall\ComposerUpstreamFiles\Manifest\ManifestFactory;
use LastCall\ComposerUpstreamFiles\TokenReplacer;
use LastCall\ComposerUpstreamFiles\FileManager;
use Symfony\Component\Console\Style\SymfonyStyle;

class ListFilesCommand extends BaseCommand
{
    public function configure()
    {
        $this->setName('upstream-files:list');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $composer = $this->getComposer(TRUE);
        $package = $composer->getPackage();
        $repository = $composer->getRepositoryManager()->getLocalRepository();
        $rfs = Factory::createRemoteFilesystem($this->getIO(), $this->getComposer()->getConfig());

        $manifestFactory = new ManifestFactory($rfs);
        $tokenReplacer = new TokenReplacer($repository);
        $manager = new FileManager($tokenReplacer, $manifestFactory);
        $manifest = $manifestFactory->fromPackage($package);

        $io = new SymfonyStyle($input, $output);
        $io->title('Upstream Files:');
        $rows = [];
          foreach($manager->getFiles($manifest) as $src => $dest) {
            $rows[] = [$dest, $src];
          }
        $io->table(['Destination', 'Source'], $rows);
    }

}
