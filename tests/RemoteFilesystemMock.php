<?php

/*
 * This file is part of Composer Upstream Files.
 * (c) 2017 Last Call Media, Rob Bayliss <rob@lastcallmedia.com>
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace LastCall\ComposerUpstreamFiles\Tests;

use Composer\Util\RemoteFilesystem;
use Composer\Downloader\TransportException;

/**
 * Remote filesystem mock.
 */
class RemoteFilesystemMock extends RemoteFilesystem
{
    /**
     * @param array $contentMap associative array of locations and content
     */
    public function __construct(array $contentMap = [])
    {
        $this->contentMap = $contentMap;
    }

    public function getContents($originUrl, $fileUrl, $progress = true, $options = array())
    {
        if (!empty($this->contentMap[$fileUrl])) {
            return $this->contentMap[$fileUrl];
        }

        throw new TransportException('The "'.$fileUrl.'" file could not be downloaded (NOT FOUND)', 404);
    }
}
