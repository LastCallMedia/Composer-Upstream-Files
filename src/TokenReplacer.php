<?php

/*
 * This file is part of Composer Upstream Files.
 * (c) 2017 Last Call Media, Rob Bayliss <rob@lastcallmedia.com>
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace LastCall\ComposerUpstreamFiles;

use Composer\Package\PackageInterface;
use Composer\Repository\RepositoryInterface;

class TokenReplacer
{
    private $repository;

    public function __construct(RepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function replace($string, array $tokens = [])
    {
        return $this->doReplace($string, $tokens);
    }

    protected function doReplace($string, array $tokens, $depth = 0)
    {
        return preg_replace_callback('/{{(\S+)}}/U', function ($matches) use ($tokens, $depth) {
            return $this->resolve($matches[1], $tokens, $depth);
        }, $string);
    }

    protected function resolve($token, array $tokens, $depth)
    {
        if ($depth > 5) {
            throw new \RuntimeException(sprintf('Infinite loop detected in %s token', $token));
        }
        // Handle simple tokens, which can be nested.
        if (isset($tokens[$token])) {
            return $this->doReplace($tokens[$token], $tokens, $depth + 1);
        }
        if (false !== strpos($token, '.')) {
            return $this->resolveRepositoryToken($token);
        }
        throw new \RuntimeException(sprintf('Unknown token %s', $token));
    }

    protected function resolveRepositoryToken($token)
    {
        list($packageName, $propertyPath) = explode('.', $token, 2);
        if ($package = $this->repository->findPackage($packageName, '*')) {
            return $this->resolvePackageProperty($package, $propertyPath);
        }
        throw new \RuntimeException(sprintf('Unknown package: %s', $packageName));
    }

    protected function resolvePackageProperty(PackageInterface $package, $property)
    {
        switch ($property) {
      case 'version':
        return $package->getPrettyVersion();
      default:
        throw new \RuntimeException(sprintf('Invalid property %s', $property));
    }
    }
}
