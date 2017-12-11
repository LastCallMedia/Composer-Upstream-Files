<?php
/**
 * Created by PhpStorm.
 * User: rbayliss
 * Date: 12/11/17
 * Time: 5:56 PM
 */

namespace LastCall\ComposerUpstreamFiles;


class ExcludeSet
{
    public function __construct(array $patterns = [])
    {
        $this->patterns = $patterns;
    }

    public function matches($uri) {
        foreach($this->patterns as $pattern) {
            if(preg_match($pattern, $uri)) {
                return TRUE;
            }
        }
        return FALSE;
    }
}