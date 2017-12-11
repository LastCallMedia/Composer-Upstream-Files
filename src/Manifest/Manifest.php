<?php
/**
 * Created by PhpStorm.
 * User: rbayliss
 * Date: 12/11/17
 * Time: 5:13 PM
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
        $this->sourceExcludes = $sourceExcludes ? $sourceExcludes : new ExcludeSet( );
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
}