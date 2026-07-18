<?php

namespace App\Services;

use App\Models\Document;
use App\Models\DocumentChunk;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class DocumentIngestionService
{
    public function __construct(
        private readonly DocumentTextExtractor $extractor,
        private readonly TextChunker $chunker,
        private readonly LocalVectorizer $vectorizer,
    ) {}

    public function ingest(UploadedFile $file, ?int $categoryId = null, ?string $name = null): Document
    {
        $text = $this->extractor->extract($file);
        $chunks = $this->chunker->split($text);
        $storedName = Str::uuid().'.'.strtolower($file->getClientOriginalExtension());
        $storedPath = $file->storeAs('documents', $storedName, 'local');

        try {
            return DB::transaction(function () use ($file, $categoryId, $name, $chunks, $storedPath): Document {
                $document = Document::create([
                    'category_id' => $categoryId,
                    'name' => trim((string) ($name ?: pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))),
                    'original_name' => $file->getClientOriginalName(),
                    'stored_path' => $storedPath,
                    'mime_type' => $file->getClientMimeType(),
                    'size_bytes' => $file->getSize() ?: 0,
                    'status' => 'indexing',
                ]);
                foreach ($chunks as $index => $content) {
                    $vector = $this->vectorizer->embed($content);
                    DocumentChunk::create([
                        'document_id' => $document->id,
                        'content' => $content,
                        'sequence' => $index + 1,
                        'vector_json' => $vector,
                        'embedding_dimensions' => count($vector),
                        'character_count' => mb_strlen($content),
                    ]);
                }
                $document->update(['status' => 'indexed', 'chunks_count' => count($chunks), 'error_message' => null]);
                return $document->fresh(['category']);
            });
        } catch (Throwable $error) {
            Storage::disk('local')->delete($storedPath);
            throw $error;
        }
    }

    public function delete(Document $document): void
    {
        DB::transaction(fn () => $document->delete());
        Storage::disk('local')->delete($document->stored_path);
    }
}
