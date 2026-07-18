<?php

namespace App\Services;

class LocalVectorizer
{
    public function embed(string $text): array
    {
        $dimensions = max(64, (int) config('knowledge.embedding_dimensions', 256));
        $vector = array_fill(0, $dimensions, 0.0);
        foreach ($this->tokens($text) as $token) {
            $digest = hash('sha256', $token, true);
            $index = unpack('N', substr($digest, 0, 4))[1] % $dimensions;
            $vector[$index] += (ord($digest[4]) % 2 === 0) ? 1.0 : -1.0;
        }
        $norm = sqrt(array_sum(array_map(static fn (float $v): float => $v * $v, $vector)));
        if ($norm > 0.0) {
            $vector = array_map(static fn (float $v): float => round($v / $norm, 8), $vector);
        }
        return $vector;
    }

    public function tokens(string $text): array
    {
        preg_match_all('/[\p{Han}]+|[a-z0-9]+/u', mb_strtolower($text), $matches);
        $tokens = [];
        foreach ($matches[0] as $part) {
            if (preg_match('/^\p{Han}+$/u', $part)) {
                $chars = preg_split('//u', $part, -1, PREG_SPLIT_NO_EMPTY) ?: [];
                foreach ($chars as $char) { $tokens[] = $char; }
                for ($i = 0; $i + 1 < count($chars); $i++) { $tokens[] = $chars[$i].$chars[$i + 1]; }
            } else {
                $tokens[] = $part;
            }
        }
        return $tokens;
    }

    public function cosine(array $left, array $right): float
    {
        $count = min(count($left), count($right));
        $score = 0.0;
        for ($i = 0; $i < $count; $i++) { $score += (float) $left[$i] * (float) $right[$i]; }
        return $score;
    }
}
