<?php

namespace App\Services\Helper;
use Illuminate\Support\Facades\Storage;
class DownloadGenerator
{
    /**
     * @param array $paths
     * @return array
     */
    public function getLinks(array $paths): array
    {
        if (empty($paths)) {
            return [];
        }
        $links = [];
        foreach ($paths as $path) {
            if (Storage::exists('public/'.basename($path))) {
                $link['url'] = route('download.file', ['filePath' => basename($path)]);
                $link['name'] = basename($path);
                $links[] = $link;
            }
        }
        return $links;
    }
}
