<?php

return [
    'chunk_chars' => (int) env('KNOWLEDGE_CHUNK_CHARS', 700),
    'chunk_overlap' => (int) env('KNOWLEDGE_CHUNK_OVERLAP', 100),
    'embedding_dimensions' => (int) env('KNOWLEDGE_EMBEDDING_DIMENSIONS', 256),
    'search_limit' => (int) env('KNOWLEDGE_SEARCH_LIMIT', 5),
    'min_vector_score' => (float) env('KNOWLEDGE_MIN_VECTOR_SCORE', 0.20),
    'max_document_bytes' => (int) env('KNOWLEDGE_MAX_DOCUMENT_BYTES', 10485760),
    'provider' => env('MODEL_PROVIDER', 'local-extractive'),
    'base_url' => rtrim((string) env('MODEL_BASE_URL', ''), '/'),
    'api_key' => (string) env('MODEL_API_KEY', ''),
    'chat_model' => env('MODEL_CHAT_MODEL', 'local-grounded-v1'),
    'timeout_seconds' => (int) env('MODEL_TIMEOUT_SECONDS', 45),
];
