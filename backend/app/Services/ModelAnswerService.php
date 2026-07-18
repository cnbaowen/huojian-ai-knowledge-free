<?php

namespace App\Services;

use App\Models\AiCallLog;
use Illuminate\Support\Facades\Http;
use Throwable;

class ModelAnswerService
{
    public function __construct(private readonly LocalVectorizer $vectorizer) {}

    public function answer(string $question, array $matches): array
    {
        if ($matches === []) {
            return ['answer' => '未在已导入的内部知识中找到足够依据，暂不作答。', 'provider' => 'knowledge-guard', 'model' => null];
        }
        $provider = (string) config('knowledge.provider', 'local-extractive');
        if ($provider === 'openai-compatible') {
            try { return $this->openAiCompatible($question, $matches); }
            catch (Throwable $error) {
                AiCallLog::create(['provider' => $provider, 'model' => config('knowledge.chat_model'), 'status' => 'fallback', 'error_message' => mb_substr($error->getMessage(), 0, 1000)]);
                $local = $this->localGrounded($question, $matches);
                $local['provider'] = 'local-fallback';
                return $local;
            }
        }
        return $this->localGrounded($question, $matches);
    }

    private function localGrounded(string $question, array $matches): array
    {
        $tokens = array_values(array_filter(array_unique($this->vectorizer->tokens($question)), static fn (string $token): bool => mb_strlen($token) >= 2));
        $candidates = [];
        foreach (array_slice($matches, 0, 3) as $index => $match) {
            $sentences = preg_split('/(?<=[。！？!?])\s*|\n+/u', $match['chunk']->content, -1, PREG_SPLIT_NO_EMPTY) ?: [];
            foreach ($sentences as $sentence) {
                $sentence = trim(preg_replace('/\s+/u', ' ', $sentence) ?? $sentence);
                if (mb_strlen($sentence) < 8) { continue; }
                $score = 0;
                foreach ($tokens as $token) { if (mb_stripos($sentence, $token) !== false) { $score++; } }
                if ($score === 0) { continue; }
                $candidates[] = ['text' => mb_substr($sentence, 0, 260), 'score' => $score, 'number' => $index + 1];
            }
        }
        usort($candidates, static fn (array $a, array $b): int => $b['score'] <=> $a['score']);
        $parts = [];
        $seen = [];
        foreach ($candidates as $candidate) {
            if (isset($seen[$candidate['text']])) { continue; }
            $seen[$candidate['text']] = true;
            $parts[] = $candidate['text'].' ['.$candidate['number'].']';
            if (count($parts) >= 1) { break; }
        }
        if ($parts === []) { $parts[] = mb_substr($matches[0]['chunk']->content, 0, 260).' [1]'; }
        return [
            'answer' => "根据当前知识库中与“{$question}”最相关的资料：\n\n".implode("\n\n", $parts),
            'provider' => 'local-extractive',
            'model' => 'local-grounded-v1',
        ];
    }

    private function openAiCompatible(string $question, array $matches): array
    {
        $baseUrl = (string) config('knowledge.base_url');
        $apiKey = (string) config('knowledge.api_key');
        $model = (string) config('knowledge.chat_model');
        if ($baseUrl === '' || $apiKey === '' || $model === '') {
            throw new \RuntimeException('OpenAI 兼容模型配置不完整。');
        }
        $sources = [];
        foreach ($matches as $index => $match) {
            $sources[] = '['.($index + 1).'] '.$match['chunk']->document->name."\n".$match['chunk']->content;
        }
        $started = microtime(true);
        $response = Http::withToken($apiKey)
            ->acceptJson()
            ->timeout((int) config('knowledge.timeout_seconds', 45))
            ->post($baseUrl.'/chat/completions', [
                'model' => $model,
                'temperature' => 0.1,
                'messages' => [
                    ['role' => 'system', 'content' => '你是企业内部知识问答助手。只能依据给定资料回答；资料不足就明确说不知道。每个关键结论必须标注对应的 [编号]，不得编造来源。'],
                    ['role' => 'user', 'content' => "问题：{$question}\n\n内部资料：\n".implode("\n\n", $sources)],
                ],
            ])->throw();
        $answer = trim((string) data_get($response->json(), 'choices.0.message.content', ''));
        if ($answer === '') { throw new \RuntimeException('模型返回了空回答。'); }
        AiCallLog::create([
            'provider' => 'openai-compatible', 'model' => $model,
            'latency_ms' => (int) round((microtime(true) - $started) * 1000), 'status' => 'success',
        ]);
        return ['answer' => $answer, 'provider' => 'openai-compatible', 'model' => $model];
    }
}
