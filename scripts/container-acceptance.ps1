param(
    [switch] $KeepRunning,
    [int] $FrontendPort = 18080,
    [int] $BackendPort = 18000
)

$ErrorActionPreference = 'Stop'
Set-StrictMode -Version Latest

$Root = [System.IO.Path]::GetFullPath((Join-Path $PSScriptRoot '..')).TrimEnd('\')
$EnvFile = Join-Path $Root '.env.container-acceptance'
$UploadResponse = Join-Path $Root 'container-upload.json'
$DemoFile = Join-Path $Root 'demo-data\company-travel-policy.md'
$Token = 'phase4-container-acceptance-not-a-production-secret'
$ProjectName = 'huojian-ai-knowledge-free'
$passed = $false

$CommercialRoot = [Environment]::GetEnvironmentVariable('COMMERCIAL_EDITION_ROOT')
if ($CommercialRoot) {
    $CommercialRoot = [System.IO.Path]::GetFullPath($CommercialRoot).TrimEnd('\')
    if ($Root -ieq $CommercialRoot -or $Root.StartsWith($CommercialRoot + '\', [StringComparison]::OrdinalIgnoreCase)) {
        throw 'Container acceptance must stay outside the commercial repository.'
    }
}
$compose = Get-Content -LiteralPath (Join-Path $Root 'docker-compose.yml') -Raw -Encoding UTF8
if (-not $compose.Contains("name: $ProjectName")) { throw 'Unexpected Compose project name.' }
foreach ($port in @($FrontendPort, $BackendPort)) {
    if (Get-NetTCPConnection -LocalPort $port -State Listen -ErrorAction SilentlyContinue) { throw "Port $port is already in use." }
}

$rng = New-Object System.Security.Cryptography.RNGCryptoServiceProvider
$keyBytes = New-Object byte[] 32
$rng.GetBytes($keyBytes)
$rng.Dispose()
$appKey = 'base64:' + [Convert]::ToBase64String($keyBytes)
$password = [Convert]::ToBase64String($keyBytes).Replace('=', '')
$envText = @"
APP_NAME=Huojian AI Knowledge Free
APP_ENV=production
APP_KEY=$appKey
APP_DEBUG=false
APP_URL=http://localhost:$BackendPort
FRONTEND_URL=http://localhost:$FrontendPort
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=huojian_free
DB_USERNAME=huojian_free
DB_PASSWORD=$password
DB_ROOT_PASSWORD=root-$password
FREE_API_TOKEN=$Token
FILESYSTEM_DISK=local
MODEL_PROVIDER=local-extractive
MODEL_CHAT_MODEL=local-grounded-v1
VITE_API_BASE_URL=http://localhost:$BackendPort/api
"@
[IO.File]::WriteAllText($EnvFile, $envText, (New-Object Text.UTF8Encoding($false)))
$env:FREE_ENV_FILE = Split-Path -Leaf $EnvFile

function Compose {
    & docker compose --env-file $EnvFile @args
    if ($LASTEXITCODE -ne 0) { throw "Docker Compose failed: $($args -join ' ')" }
}

function Remove-FreeOrphans {
    $existingVolumes = @(& docker volume ls --format '{{.Name}}')
    foreach ($volume in @('huojian-ai-knowledge-free-mysql', 'huojian-ai-knowledge-free-documents')) {
        if ($volume -notin $existingVolumes) { continue }
        $raw = & docker volume inspect $volume 2>$null
        if ($LASTEXITCODE -eq 0) {
            $info = @($raw | ConvertFrom-Json)
            if ($info[0].Labels.'com.docker.compose.project' -eq $ProjectName) { & docker volume rm $volume | Out-Null }
        }
    }
    $existingNetworks = @(& docker network ls --format '{{.Name}}')
    if ('huojian-ai-knowledge-free-net' -in $existingNetworks) {
        $networkRaw = & docker network inspect 'huojian-ai-knowledge-free-net'
        $networkInfo = @($networkRaw | ConvertFrom-Json)
        if ($networkInfo[0].Labels.'com.docker.compose.project' -eq $ProjectName) { & docker network rm 'huojian-ai-knowledge-free-net' | Out-Null }
    }
}

function Wait-Health {
    $base = "http://127.0.0.1:$BackendPort/api"
    for ($attempt = 0; $attempt -lt 80; $attempt++) {
        try { $health = Invoke-RestMethod -Uri "$base/health" -Headers @{ Accept = 'application/json' }; if ($health.status -eq 'ok') { return } } catch {}
        Start-Sleep -Milliseconds 500
    }
    throw 'Container backend health check timed out.'
}

try {
    Compose down -v --remove-orphans
    Remove-FreeOrphans
    Compose up -d --build
    Wait-Health
    $migrated = $false
    for ($attempt = 0; $attempt -lt 40; $attempt++) {
        & docker compose --env-file $EnvFile exec -T backend php artisan migrate --force
        if ($LASTEXITCODE -eq 0) { $migrated = $true; break }
        Start-Sleep -Seconds 1
    }
    if (-not $migrated) { throw 'Database migration did not become ready.' }

    $base = "http://127.0.0.1:$BackendPort/api"
    $headers = @{ Authorization = "Bearer $Token"; Accept = 'application/json' }
    $category = Invoke-RestMethod -Method Post -Uri "$base/categories" -Headers $headers -ContentType 'application/json' -Body '{"name":"Container Demo"}'
    & curl.exe -fsS -o $UploadResponse -X POST -H "Authorization: Bearer $Token" -H 'Accept: application/json' -F "file=@$DemoFile;type=text/markdown" -F "category_id=$($category.data.id)" "$base/documents"
    if ($LASTEXITCODE -ne 0) { throw 'Container document upload failed.' }
    $upload = Get-Content -LiteralPath $UploadResponse -Raw -Encoding UTF8 | ConvertFrom-Json
    if ($upload.data.status -ne 'indexed' -or $upload.data.chunks_count -lt 1) { throw 'Container document indexing failed.' }

    $question = @{ question = 'What is the reimbursement deadline?'; category_id = $category.data.id } | ConvertTo-Json
    $qa = Invoke-RestMethod -Method Post -Uri "$base/knowledge-chat/ask" -Headers $headers -ContentType 'application/json' -Body $question
    if ($qa.citations.Count -lt 1 -or $qa.answer -notmatch '25') { throw 'Container grounded answer failed.' }
    $quality = Invoke-RestMethod -Uri "$base/knowledge-quality" -Headers $headers
    if ($quality.data.quality_score -ne 100 -or $quality.data.issues_count -ne 0) { throw 'Knowledge quality acceptance failed.' }
    $evaluation = Invoke-RestMethod -Method Post -Uri "$base/rag-evaluations/run" -Headers $headers -ContentType 'application/json' -Body (@{ question = 'What is the reimbursement deadline?'; expected_keyword = '25' } | ConvertTo-Json)
    if (-not $evaluation.data.passed -or $evaluation.data.citations_count -lt 1) { throw 'RAG evaluation acceptance failed.' }
    $robot = Invoke-RestMethod -Method Post -Uri "$base/wecom-configs" -Headers $headers -ContentType 'application/json' -Body (@{ name = 'Acceptance Robot'; webhook_url = 'https://qyapi.weixin.qq.com/cgi-bin/webhook/send?key=acceptance-placeholder'; enabled = $true } | ConvertTo-Json)
    if (-not $robot.data.webhook_configured -or $robot.data.PSObject.Properties.Name -contains 'webhook_url') { throw 'WeCom secret exposure check failed.' }
    $robotValidation = Invoke-RestMethod -Method Post -Uri "$base/wecom-configs/$($robot.data.id)/test" -Headers $headers -ContentType 'application/json' -Body '{"send_test_message":false}'
    if (-not $robotValidation.ok -or $robotValidation.mode -ne 'validation') { throw 'WeCom robot validation failed.' }

    Compose restart mysql
    Compose up -d --force-recreate backend
    Wait-Health
    $documents = $null
    for ($attempt = 0; $attempt -lt 40; $attempt++) {
        try { $documents = Invoke-RestMethod -Uri "$base/documents" -Headers $headers; break } catch { Start-Sleep -Seconds 1 }
    }
    if (-not $documents) { throw 'Database API did not recover after restart.' }
    $persisted = @($documents.data | Where-Object { $_.id -eq $upload.data.id })
    if ($persisted.Count -ne 1 -or $persisted[0].status -ne 'indexed') { throw 'Database persistence check failed.' }
    $storedFiles = @(& docker compose --env-file $EnvFile exec -T backend find /app/storage/app/private/documents -maxdepth 1 -type f)
    if ($LASTEXITCODE -ne 0 -or $storedFiles.Count -lt 1) { throw 'Document volume persistence check failed.' }
    $qaAfter = Invoke-RestMethod -Method Post -Uri "$base/knowledge-chat/ask" -Headers $headers -ContentType 'application/json' -Body $question
    if ($qaAfter.citations.Count -lt 1 -or $qaAfter.answer -notmatch '25') { throw 'Post-recreate knowledge answer failed.' }

    $passed = $true
    [ordered]@{
        passed = $true
        compose_project = $ProjectName
        document_status = $upload.data.status
        chunks = $upload.data.chunks_count
        database_persisted = $true
        document_volume_persisted = $true
        answer_after_recreate = $true
        knowledge_quality_passed = $true
        rag_evaluation_passed = $true
        wecom_robot_config_passed = $true
        commercial_runtime_used = $false
        kept_running = [bool] $KeepRunning
    } | ConvertTo-Json
} finally {
    if (-not $KeepRunning -or -not $passed) {
        try { Compose down -v --remove-orphans } catch { Write-Warning $_.Exception.Message }
        Remove-FreeOrphans
        Remove-Item -LiteralPath $EnvFile -Force -ErrorAction SilentlyContinue
    }
    Remove-Item -LiteralPath $UploadResponse -Force -ErrorAction SilentlyContinue
}
