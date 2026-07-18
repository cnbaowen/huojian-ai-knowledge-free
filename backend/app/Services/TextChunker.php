<?php

namespace App\Services;

class TextChunker
{
    public function split(string $text): array
    {
        $size = max(200, (int) config('knowledge.chunk_chars', 700));
        $overlap = min(max(0, (int) config('knowledge.chunk_overlap', 100)), intdiv($size, 2));
        $length = mb_strlen($text);
        $chunks = [];
        for ($start = 0; $start < $length;) {
            $take = min($size, $length - $start);
            $piece = trim(mb_substr($text, $start, $take));
            if ($piece !== '') {
                $chunks[] = $piece;
            }
            if ($start + $take >= $length) {
                break;
            }
            $start += max(1, $take - $overlap);
        }
        return $chunks;
    }
}
