<?php
/**
 * Created by PhpStorm.
 * User: rbayliss
 * Date: 12/11/17
 * Time: 6:36 PM
 */

namespace LastCall\ComposerUpstreamFiles\Command;

use Composer\Util\RemoteFilesystem;
use LastCall\ComposerUpstreamFiles\FileManager;
use LastCall\ComposerUpstreamFiles\ManifestFactory;
use LastCall\ComposerUpstreamFiles\TokenReplacer;

trait UpstreamFilesCommandTrait
{


    protected function getFileManager(RemoteFilesystem $rfs) {
        $repository = $this->getComposer()->getRepositoryManager()->getLocalRepository();
        $manifestFactory = new ManifestFactory($rfs);
        $tokenReplacer = new TokenReplacer($repository);
        return new FileManager($tokenReplacer, $manifestFactory);
    }

}