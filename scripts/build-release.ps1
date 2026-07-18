param([string] $Version = '')

$ErrorActionPreference = 'Stop'
Set-StrictMode -Version Latest

$Root = [System.IO.Path]::GetFullPath((Join-Path $PSScriptRoot '..')).TrimEnd('\')
Push-Location $Root
try {
    if (-not $Version) { $Version = (& git describe --tags --exact-match HEAD 2>$null) }
    if (-not $Version) { throw 'Version or exact Git tag is required.' }
    if (@(& git status --porcelain).Count -ne 0) { throw 'Working tree must be clean.' }
    if (Test-Path (Join-Path $Root '.env')) { throw '.env must not be included in a release.' }
    foreach ($Required in @('LICENSE', 'NOTICE', 'SECURITY.md')) {
        if (-not (Test-Path (Join-Path $Root $Required))) { throw "Missing release file: $Required" }
    }
    foreach ($Forbidden in @('customers', 'industry-templates', 'packaging', 'release', 'deploy', 'docker-data', 'runtime-logs', 'backups', 'artifacts', 'test-results')) {
        if (Test-Path (Join-Path $Root $Forbidden)) { throw "Forbidden release path: $Forbidden" }
    }

    $Output = Join-Path $Root 'output\release'
    New-Item -ItemType Directory -Force $Output | Out-Null
    $Archive = Join-Path $Output "huojian-ai-knowledge-free-$Version.zip"
    & git archive --format=zip --prefix="huojian-ai-knowledge-free-$Version/" -o $Archive HEAD
    if ($LASTEXITCODE -ne 0) { throw 'git archive failed.' }

    $Hash = (Get-FileHash -LiteralPath $Archive -Algorithm SHA256).Hash.ToLowerInvariant()
    $Checksum = "$Hash  $([System.IO.Path]::GetFileName($Archive))`n"
    [System.IO.File]::WriteAllText((Join-Path $Output 'SHA256SUMS.txt'), $Checksum, (New-Object Text.UTF8Encoding($false)))
    Write-Output $Archive
} finally {
    Pop-Location
}
