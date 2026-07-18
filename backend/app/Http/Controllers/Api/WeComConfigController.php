<?php

namespace App\Http\Controllers\Api;

use App\Models\WeComConfig;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;

class WeComConfigController
{
    public function index(): JsonResponse
    {
        return response()->json(['data' => WeComConfig::query()->latest()->get()->map(fn (WeComConfig $config) => $this->present($config))]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:100'], 'webhook_url' => ['required', 'url:https', 'max:1000'], 'enabled' => ['sometimes', 'boolean']]);
        $this->assertOfficialWebhook($data['webhook_url']);
        $config = WeComConfig::create(['name' => $data['name'], 'encrypted_webhook' => Crypt::encryptString($data['webhook_url']), 'enabled' => (bool) ($data['enabled'] ?? false)]);
        return response()->json(['data' => $this->present($config)], 201);
    }

    public function update(Request $request, WeComConfig $weComConfig): JsonResponse
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:100'], 'webhook_url' => ['nullable', 'url:https', 'max:1000'], 'enabled' => ['sometimes', 'boolean']]);
        $weComConfig->name = $data['name'];
        $weComConfig->enabled = (bool) ($data['enabled'] ?? false);
        if (! empty($data['webhook_url'])) { $this->assertOfficialWebhook($data['webhook_url']); $weComConfig->encrypted_webhook = Crypt::encryptString($data['webhook_url']); }
        $weComConfig->save();
        return response()->json(['data' => $this->present($weComConfig)]);
    }

    public function destroy(WeComConfig $weComConfig): JsonResponse
    {
        $weComConfig->delete();
        return response()->json(['message' => '企业微信机器人配置已删除。']);
    }

    public function test(Request $request, WeComConfig $weComConfig): JsonResponse
    {
        $url = Crypt::decryptString($weComConfig->encrypted_webhook);
        $this->assertOfficialWebhook($url);
        if (! $request->boolean('send_test_message')) return response()->json(['ok' => true, 'mode' => 'validation', 'message' => 'Webhook 格式有效；未发送测试消息。']);
        try {
            $response = Http::timeout(8)->post($url, ['msgtype' => 'text', 'text' => ['content' => '火建AI知识管理免费版：企业微信机器人连接测试成功。']]);
            $payload = $response->json();
            $ok = $response->successful() && (int) ($payload['errcode'] ?? -1) === 0;
            return $this->recordTest($weComConfig, $ok, $ok ? '测试消息发送成功。' : (string) ($payload['errmsg'] ?? '企业微信拒绝了测试消息。'));
        } catch (ConnectionException) {
            return $this->recordTest($weComConfig, false, '无法连接企业微信官方接口。');
        }
    }

    private function assertOfficialWebhook(string $url): void
    {
        $parts = parse_url($url);
        if (($parts['scheme'] ?? '') !== 'https' || ($parts['host'] ?? '') !== 'qyapi.weixin.qq.com' || ! str_starts_with($parts['path'] ?? '', '/cgi-bin/webhook/send')) abort(422, '只允许企业微信官方机器人 Webhook。');
    }

    private function present(WeComConfig $config): array
    {
        return ['id' => $config->id, 'name' => $config->name, 'enabled' => $config->enabled, 'webhook_configured' => true, 'last_tested_at' => $config->last_tested_at, 'last_test_status' => $config->last_test_status, 'last_test_message' => $config->last_test_message];
    }

    private function recordTest(WeComConfig $config, bool $ok, string $message): JsonResponse
    {
        $config->forceFill(['last_tested_at' => now(), 'last_test_status' => $ok ? 'success' : 'failed', 'last_test_message' => $message])->save();
        return response()->json(['ok' => $ok, 'mode' => 'live', 'message' => $message], $ok ? 200 : 422);
    }
}
