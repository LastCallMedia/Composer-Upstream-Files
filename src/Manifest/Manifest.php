<?php

/*
 * This file is part of Composer Upstream Files.
 * (c) 2017 Last Call Media, Rob Bayliss <rob@lastcallmedia.com>
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace LastCall\ComposerUpstreamFiles\Manifest;

use LastCall\ComposerUpstreamFiles\ExcludeSet;

class Manifest
{
    private $uri = '';

    private $files = [];

    private $tokens = [];

    private $manifests = [];

    private $sourceExcludes = [];

    private $destExcludes = [];

    public function __construct(
      $uri,
      array $files = [],
      array $tokens = [],
      array $manifests = [],
      ExcludeSet $sourceExcludes = null,
      ExcludeSet $destExcludes = null
    ) {
        $this->uri = $uri;
        $this->files = $files;
        $this->tokens = $tokens;
        $this->manifests = $manifests;
        $this->sourceExcludes = $sourceExcludes ? $sourceExcludes : new ExcludeSet();
        $this->destExcludes = $destExcludes ? $destExcludes : new ExcludeSet();
    }

    public function getUri()
    {
        return $this->uri;
    }

    public function getFiles()
    {
        return $this->files;
    }

    public function getTokens()
    {
        return $this->tokens;
    }

    public function getManifests()
    {
        return $this->manifests;
    }

    /**
     * @return \LastCall\ComposerUpstreamFiles\ExcludeSet
     */
    public function getSourceExcludes()
    {
        return $this->sourceExcludes;
    }

    /**
     * @return \LastCall\ComposerUpstreamFiles\ExcludeSet
     */
    public function getDestExcludes()
    {
        return $this->destExcludes;
    }

    public function withTokens(array $tokens)
    {
        $clone = clone $this;
        $clone->tokens = $tokens;

        return $clone;
    }
}
