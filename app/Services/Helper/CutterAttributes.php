<?php

namespace App\Services\Helper;

class CutterAttributes
{
    /**
     * $attrs = ['a', 'img', ...]
     *
     * @param string $content
     * @param array $tags
     * @return string
     */
    public function cut(string $content, array $tags): string
    {
        foreach ($tags as $tag) {
            $pattern = '/<' . $tag . '\b[^>]*>(.*?)<\/' . $tag . '>/is';
            $content = preg_replace_callback($pattern, function ($matches) {
                return $matches[1];
            }, $content);
        }

        return $content;
    }
}
