# 配置说明

## 必填安全项

- `APP_KEY`：Laravel 加密密钥。
- `DB_PASSWORD`、`DB_ROOT_PASSWORD`：独立免费版数据库密码。
- `FREE_API_TOKEN`：当前免费版登录令牌，必须使用高强度随机值。

## 本地零密钥模式

```dotenv
MODEL_PROVIDER=local-extractive
MODEL_CHAT_MODEL=local-grounded-v1
```

该模式使用本地确定性向量和引用抽取回答，适合演示和离线验证，不等同于商业级语义模型。

## OpenAI 兼容模式

```dotenv
MODEL_PROVIDER=openai-compatible
MODEL_BASE_URL=https://provider.example/v1
MODEL_API_KEY=replace-with-server-secret
MODEL_CHAT_MODEL=provider-model-name
```

密钥只应写入服务器 `.env` 或密钥管理系统。外部调用失败时系统会回退到本地引用回答。

## 知识参数

- `KNOWLEDGE_CHUNK_CHARS`：默认 700。
- `KNOWLEDGE_CHUNK_OVERLAP`：默认 100。
- `KNOWLEDGE_EMBEDDING_DIMENSIONS`：默认 256。
- `KNOWLEDGE_SEARCH_LIMIT`：默认 5。
- `KNOWLEDGE_MIN_VECTOR_SCORE`：默认 0.20。
- `KNOWLEDGE_MAX_DOCUMENT_BYTES`：默认 10485760。
