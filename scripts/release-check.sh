#!/usr/bin/env bash
set -euo pipefail

root="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$root"

for path in customers industry-templates packaging release deploy docker-data runtime-logs backups artifacts test-results; do
  [[ ! -e "$path" ]] || { echo "forbidden path: $path" >&2; exit 1; }
done
[[ ! -f .env ]] || { echo '.env must not be committed' >&2; exit 1; }
[[ -f LICENSE && -f NOTICE && -f SECURITY.md ]] || { echo 'release legal/security files missing' >&2; exit 1; }
grep -q 'Apache License' LICENSE
code_roots=(backend/app backend/routes backend/config backend/database frontend/src)
for symbol in CustomerServiceController CustomerServiceCenterService OpenClaw WechatKf SalesWecom CrmController IntelligenceController PublishController AgenticRagSupplementService CustomerServiceModelReplayStore LicenseService SystemUpdate; do
  if grep -R -F -n --include='*.php' --include='*.js' --include='*.vue' "$symbol" "${code_roots[@]}"; then
    echo "commercial symbol found: $symbol" >&2; exit 1
  fi
done

for route in /customer-service /wechat-kf /crm /sales-wecom /content /contents /publish /intelligence /openclaw /agents /public-gateway /system/updates /system/license; do
  if grep -F -n "$route" backend/routes/api.php frontend/src/router/index.js; then
    echo "commercial route found: $route" >&2; exit 1
  fi
done

if grep -R -E -n --exclude='*.lock' --exclude='release-check.sh' --exclude-dir='.git' --exclude-dir='vendor' --exclude-dir='node_modules' -- '(-----BEGIN (RSA |EC |OPENSSH )?PRIVATE KEY-----|AKIA[0-9A-Z]{16}|sk-[A-Za-z0-9_-]{20,}|gh[pousr]_[A-Za-z0-9]{20,})' .; then
  echo 'secret-like signature found' >&2; exit 1
fi

echo 'release boundary check passed'
