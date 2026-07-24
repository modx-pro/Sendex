<?php

class modParser
{
    /**
     * @param string $root
     * @param string $content
     * @param bool $processUncacheable
     * @param bool $removeUnprocessed
     * @param string $prefix
     * @param string $suffix
     * @param array $tokens
     * @param int $maxIterations
     *
     * @return bool
     */
    public function processElementTags(
        $root,
        &$content,
        $processUncacheable = false,
        $removeUnprocessed = false,
        $prefix = '[[',
        $suffix = ']]',
        array $tokens = array(),
        $maxIterations = 10
    ) {
        return true;
    }
}
