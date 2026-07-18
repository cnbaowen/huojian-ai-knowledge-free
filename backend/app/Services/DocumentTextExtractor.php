<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use InvalidArgumentException;
use ZipArchive;

class DocumentTextExtractor
{
    private const EXTENSIONS = ['txt', 'md', 'markdown', 'log', 'csv', 'json', 'docx', 'xlsx', 'pdf'];

    public function extract(UploadedFile $file): string
    {
        $extension = strtolower($file->getClientOriginalExtension());
        if (! in_array($extension, self::EXTENSIONS, true)) throw new InvalidArgumentException('支持 TXT、Markdown、LOG、CSV、JSON、DOCX、XLSX 和可复制文字 PDF。');
        $path = $file->getRealPath();
        $raw = match ($extension) {
            'docx' => $this->extractDocx($path),
            'xlsx' => $this->extractXlsx($path),
            'pdf' => $this->extractPdf($path),
            default => file_get_contents($path),
        };
        if (! is_string($raw) || $raw === '') throw new InvalidArgumentException('文档为空或无法读取。');
        if (! mb_check_encoding($raw, 'UTF-8')) $raw = mb_convert_encoding($raw, 'UTF-8', 'GB18030,GBK,BIG-5,ISO-8859-1');
        if ($extension === 'json') { $decoded = json_decode($raw, true, 128, JSON_THROW_ON_ERROR); $raw = $this->flattenJson($decoded); }
        $text = preg_replace("/\r\n?|\x{2028}|\x{2029}/u", "\n", $raw) ?? $raw;
        $text = preg_replace('/[\t ]+/u', ' ', $text) ?? $text;
        $text = preg_replace('/\n{3,}/u', "\n\n", $text) ?? $text;
        $text = trim($text);
        if ($text === '') throw new InvalidArgumentException('文档没有可索引的文本内容；扫描 PDF 请先执行 OCR。');
        return $text;
    }

    private function extractDocx(string $path): string
    {
        $zip = $this->openZip($path);
        $xml = $zip->getFromName('word/document.xml');
        $zip->close();
        if (! is_string($xml)) throw new InvalidArgumentException('DOCX 缺少正文内容。');
        return $this->xmlText($xml, ['</w:p>' => "\n", '</w:tr>' => "\n"]);
    }

    private function extractXlsx(string $path): string
    {
        $zip = $this->openZip($path);
        $shared = [];
        $sharedXml = $zip->getFromName('xl/sharedStrings.xml');
        if (is_string($sharedXml) && preg_match_all('/<si[^>]*>(.*?)<\/si>/s', $sharedXml, $items)) foreach ($items[1] as $item) $shared[] = $this->xmlText($item);
        $lines = [];
        for ($i = 1; $i <= 100; $i++) {
            $sheet = $zip->getFromName("xl/worksheets/sheet{$i}.xml");
            if (! is_string($sheet)) continue;
            if (preg_match_all('/<row[^>]*>(.*?)<\/row>/s', $sheet, $rows)) foreach ($rows[1] as $row) {
                $cells = [];
                if (preg_match_all('/<c([^>]*)>(.*?)<\/c>/s', $row, $matches, PREG_SET_ORDER)) foreach ($matches as $match) {
                    preg_match('/<v>(.*?)<\/v>/s', $match[2], $value); $cell = html_entity_decode($value[1] ?? '', ENT_QUOTES | ENT_XML1, 'UTF-8');
                    if (str_contains($match[1], 't="s"')) $cell = $shared[(int) $cell] ?? $cell;
                    $cells[] = trim($cell);
                }
                if ($cells) $lines[] = implode(' | ', $cells);
            }
        }
        $zip->close();
        return implode("\n", $lines);
    }

    private function extractPdf(string $path): string
    {
        $process = @proc_open(['pdftotext', '-layout', $path, '-'], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
        if (! is_resource($process)) throw new InvalidArgumentException('当前环境缺少 PDF 文本解析器。');
        $text = stream_get_contents($pipes[1]); $error = stream_get_contents($pipes[2]); fclose($pipes[1]); fclose($pipes[2]);
        $code = proc_close($process);
        if ($code !== 0 || trim((string) $text) === '') throw new InvalidArgumentException('PDF 无可复制文字或解析失败；扫描 PDF 请先执行 OCR。'.($error ? ' '.$error : ''));
        return (string) $text;
    }

    private function openZip(string $path): ZipArchive
    {
        if (! class_exists(ZipArchive::class)) throw new InvalidArgumentException('当前环境缺少 ZIP 扩展，无法解析 Office 文档。');
        $zip = new ZipArchive();
        if ($zip->open($path) !== true) throw new InvalidArgumentException('Office 文档结构无效。');
        return $zip;
    }

    private function xmlText(string $xml, array $replacements = []): string
    {
        $xml = str_replace(array_keys($replacements), array_values($replacements), $xml);
        return html_entity_decode(strip_tags($xml), ENT_QUOTES | ENT_XML1, 'UTF-8');
    }

    private function flattenJson(mixed $value, string $prefix = ''): string
    {
        if (! is_array($value)) return trim($prefix.' '.(is_scalar($value) ? (string) $value : ''));
        $lines = []; foreach ($value as $key => $child) { $path = $prefix === '' ? (string) $key : $prefix.'.'.$key; $lines[] = $this->flattenJson($child, $path); }
        return implode("\n", array_filter($lines));
    }
}
