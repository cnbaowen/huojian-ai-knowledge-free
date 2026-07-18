param([int] $Port = 18000)

$ErrorActionPreference = 'Stop'
Set-StrictMode -Version Latest

$Root = [System.IO.Path]::GetFullPath((Join-Path $PSScriptRoot '..')).TrimEnd('\')
$Backend = Join-Path $Root 'backend'
$Database = Join-Path $Backend 'database\acceptance.sqlite'
$Sample = Join-Path $Root 'acceptance-knowledge.txt'
$Stdout = Join-Path $Backend 'storage\logs\acceptance-server.out.log'
$Stderr = Join-Path $Backend 'storage\logs\acceptance-server.err.log'
$UploadResponse = Join-Path $Root 'acceptance-upload.json'
$Token = 'phase3-acceptance-token-not-a-production-secret'
$server = $null

if (-not $Database.StartsWith($Backend + '\', [System.StringComparison]::OrdinalIgnoreCase)) { throw 'Unsafe acceptance database path.' }
if (Get-NetTCPConnection -LocalPort $Port -State Listen -ErrorAction SilentlyContinue) { throw "Port $Port is already in use." }

try {
    if (-not (Test-Path (Join-Path $Backend 'vendor\autoload.php'))) {
        & composer install --working-dir=$Backend --no-interaction --prefer-dist
        if ($LASTEXITCODE -ne 0) { throw 'Composer install failed.' }
    }
    if (Test-Path -LiteralPath $Database) { Remove-Item -LiteralPath $Database -Force }
    [System.IO.File]::WriteAllBytes($Database, [byte[]]@())
    $sampleText = [Text.Encoding]::UTF8.GetString([Convert]::FromBase64String('54Gr5bu656eR5oqA5beu5peF5oql6ZSA5Yi25bqmCuWRmOW3peW6lOWcqOavj+aciDI15pel5YmN5o+Q5Lqk5beu5peF5oql6ZSA5Y2V44CC5Y2V56yU6LaF6L+HNTAwMOWFg+mcgOimgemDqOmXqOi0n+i0o+S6uuWuoeaJueOAguaKpemUgOadkOaWmeW/hemhu+WMheWQq+WPkeelqOWSjOWHuuW3ruWuoeaJueiusOW9leOAgg=='))
    $sampleText = (($sampleText + [Environment]::NewLine) * 12).Trim()
    [System.IO.File]::WriteAllText($Sample, $sampleText, (New-Object System.Text.UTF8Encoding($false)))

    $rng = New-Object System.Security.Cryptography.RNGCryptoServiceProvider
    $keyBytes = New-Object byte[] 32
    $rng.GetBytes($keyBytes)
    $rng.Dispose()
    $env:APP_ENV = 'testing'
    $env:APP_DEBUG = 'false'
    $env:APP_KEY = 'base64:' + [Convert]::ToBase64String($keyBytes)
    $env:DB_CONNECTION = 'sqlite'
    $env:DB_DATABASE = $Database
    $env:FREE_API_TOKEN = $Token
    $env:MODEL_PROVIDER = 'local-extractive'
    $env:FILESYSTEM_DISK = 'local'

    Push-Location $Backend
    try {
        & php artisan migrate --force
        if ($LASTEXITCODE -ne 0) { throw 'Migration failed.' }
    } finally { Pop-Location }

    $php = (Get-Command php -ErrorAction Stop).Source
    $server = Start-Process -FilePath $php -ArgumentList @('artisan', 'serve', '--host=127.0.0.1', "--port=$Port") -WorkingDirectory $Backend -WindowStyle Hidden -RedirectStandardOutput $Stdout -RedirectStandardError $Stderr -PassThru
    $base = "http://127.0.0.1:$Port/api"
    $healthy = $false
    for ($attempt = 0; $attempt -lt 30; $attempt++) {
        try { $health = Invoke-RestMethod -Uri "$base/health" -Headers @{ Accept = 'application/json' }; $healthy = $health.status -eq 'ok'; if ($healthy) { break } } catch {}
        Start-Sleep -Milliseconds 300
    }
    if (-not $healthy) { throw 'Backend health check did not become ready.' }

    $headers = @{ Authorization = "Bearer $Token"; Accept = 'application/json' }
    $categoryName = [Text.Encoding]::UTF8.GetString([Convert]::FromBase64String('5Yi25bqm6KeE6IyD'))
    $categoryBody = [Text.Encoding]::UTF8.GetBytes((@{ name = $categoryName } | ConvertTo-Json))
    $category = Invoke-RestMethod -Method Post -Uri "$base/categories" -Headers $headers -ContentType 'application/json; charset=utf-8' -Body $categoryBody
    & curl.exe -sS -o $UploadResponse -X POST -H "Authorization: Bearer $Token" -H 'Accept: application/json' -F "file=@$Sample;type=text/plain" -F "category_id=$($category.data.id)" "$base/documents"
    if ($LASTEXITCODE -ne 0) { throw 'Document upload request failed.' }
    $upload = Get-Content -LiteralPath $UploadResponse -Raw -Encoding UTF8 | ConvertFrom-Json
    if (-not $upload.data -or $upload.data.status -ne 'indexed' -or $upload.data.chunks_count -lt 2) { throw "Document was not split and indexed. Response: $(Get-Content -LiteralPath $UploadResponse -Raw -Encoding UTF8)" }

    $questionText = [Text.Encoding]::UTF8.GetString([Convert]::FromBase64String('5beu5peF5oql6ZSA6ZyA6KaB5Zyo5LuA5LmI5pe25YCZ5o+Q5Lqk77yf'))
    $question = [Text.Encoding]::UTF8.GetBytes((@{ question = $questionText; category_id = $category.data.id } | ConvertTo-Json))
    $qa = Invoke-RestMethod -Method Post -Uri "$base/knowledge-chat/ask" -Headers $headers -ContentType 'application/json; charset=utf-8' -Body $question
    if (-not $qa.ready -or $qa.citations.Count -lt 1) { throw 'Question answering did not return citations.' }
    if ($qa.answer -notmatch '25') { throw 'Grounded answer did not contain the expected knowledge fact.' }

    $unknownText = [Text.Encoding]::UTF8.GetString([Convert]::FromBase64String('6YeP5a2Q57qg57yg5a6e6aqM57uT6K665piv5LuA5LmI77yf'))
    $unknownBody = [Text.Encoding]::UTF8.GetBytes((@{ question = $unknownText; category_id = $category.data.id } | ConvertTo-Json))
    $unknown = Invoke-RestMethod -Method Post -Uri "$base/knowledge-chat/ask" -Headers $headers -ContentType 'application/json; charset=utf-8' -Body $unknownBody
    if ($unknown.citations.Count -ne 0 -or $unknown.provider -ne 'knowledge-guard') { throw 'Unsupported question was not refused by the knowledge guard.' }

    $feedback = Invoke-RestMethod -Method Post -Uri "$base/knowledge-chat/feedback" -Headers $headers -ContentType 'application/json' -Body (@{ message_id = $qa.message_id; rating = 'up'; note = 'acceptance' } | ConvertTo-Json)
    if (-not $feedback.accepted) { throw 'Feedback was not accepted.' }
    $docs = Invoke-RestMethod -Uri "$base/documents" -Headers $headers
    if ($docs.data.Count -ne 1) { throw 'Document list count mismatch.' }
    Invoke-RestMethod -Method Delete -Uri "$base/documents/$($upload.data.id)" -Headers $headers | Out-Null

    [ordered]@{
        passed = $true
        stage = 'phase-3-rag-closed-loop'
        document_status = $upload.data.status
        chunks = $upload.data.chunks_count
        retrieved_chunks = $qa.retrieved_chunks
        citation_document = $qa.citations[0].document_name
        provider = $qa.provider
        feedback_saved = $feedback.accepted
        unsupported_question_refused = $true
        commercial_runtime_used = $false
    } | ConvertTo-Json
} catch {
    Write-Error $_.Exception.ToString()
    throw
} finally {
    if ($server -and -not $server.HasExited) { Stop-Process -Id $server.Id -Force -ErrorAction SilentlyContinue; $server.WaitForExit() }
    $listeners = @(Get-NetTCPConnection -LocalPort $Port -State Listen -ErrorAction SilentlyContinue)
    foreach ($listener in $listeners) {
        $process = Get-CimInstance Win32_Process -Filter "ProcessId=$($listener.OwningProcess)"
        if ($process.CommandLine -like "*$Backend*") { Stop-Process -Id $listener.OwningProcess -Force -ErrorAction SilentlyContinue }
    }
    if (Test-Path -LiteralPath $Sample) { Remove-Item -LiteralPath $Sample -Force }
    if (Test-Path -LiteralPath $UploadResponse) { Remove-Item -LiteralPath $UploadResponse -Force }
    if (Test-Path -LiteralPath $Database) { Remove-Item -LiteralPath $Database -Force }
    $documentRoot = Join-Path $Backend 'storage\app\private\documents'
    Get-ChildItem -LiteralPath $documentRoot -Force -File -ErrorAction SilentlyContinue | Where-Object { $_.Name -ne '.gitkeep' } | Remove-Item -Force
}
