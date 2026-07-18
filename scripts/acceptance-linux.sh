#!/usr/bin/env bash
set -euo pipefail

root="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
backend="$root/backend"
database="$backend/database/acceptance.sqlite"
upload_json="$root/acceptance-upload.json"
server_log="$backend/storage/logs/acceptance-linux.log"
token='phase4-linux-acceptance-not-a-production-secret'
port="${ACCEPTANCE_PORT:-18000}"
server_pid=''

cleanup() {
  if [[ -n "$server_pid" ]]; then kill -- "-$server_pid" 2>/dev/null || true; wait "$server_pid" 2>/dev/null || true; fi
  rm -f "$database" "$upload_json"
  find "$backend/storage/app/private/documents" -maxdepth 1 -type f ! -name '.gitkeep' -delete 2>/dev/null || true
}
trap cleanup EXIT

[[ ! -e /proc/1/cwd || "$database" == "$backend"/* ]] || { echo 'unsafe database path' >&2; exit 1; }
[[ -f "$backend/vendor/autoload.php" ]] || composer --working-dir="$backend" install --no-interaction --prefer-dist
: > "$database"

export APP_ENV=testing APP_DEBUG=false
export APP_KEY="base64:$(php -r 'echo base64_encode(random_bytes(32));')"
export DB_CONNECTION=sqlite DB_DATABASE="$database"
export FREE_API_TOKEN="$token" MODEL_PROVIDER=local-extractive FILESYSTEM_DISK=local

php "$backend/artisan" migrate --force
cd "$backend"
setsid php artisan serve --host=127.0.0.1 --port="$port" >"$server_log" 2>&1 &
server_pid=$!
base="http://127.0.0.1:$port/api"
for _ in $(seq 1 40); do curl -fsS "$base/health" >/dev/null 2>&1 && break; sleep 0.25; done
curl -fsS "$base/health" >/dev/null
auth=(-H "Authorization: Bearer $token" -H 'Accept: application/json')

category="$(curl -fsS -X POST "${auth[@]}" -H 'Content-Type: application/json' --data '{"name":"Demo Policy"}' "$base/categories")"
category_id="$(python3 -c 'import json,sys; print(json.load(sys.stdin)["data"]["id"])' <<<"$category")"
curl -fsS -o "$upload_json" -X POST "${auth[@]}" -F "file=@$root/demo-data/company-travel-policy.md;type=text/markdown" -F "category_id=$category_id" "$base/documents"
document_id="$(python3 -c 'import json,sys; d=json.load(sys.stdin)["data"]; assert d["status"]=="indexed" and d["chunks_count"]>=1; print(d["id"])' <"$upload_json")"

question_body="$(python3 -c 'import json,sys; print(json.dumps({"question":"What is the reimbursement deadline?","category_id":int(sys.argv[1])}))' "$category_id")"
qa="$(curl -fsS -X POST "${auth[@]}" -H 'Content-Type: application/json' --data "$question_body" "$base/knowledge-chat/ask")"
message_id="$(python3 -c 'import json,sys; d=json.load(sys.stdin); assert d["ready"] and len(d["citations"])>=1 and "25" in d["answer"]; print(d["message_id"])' <<<"$qa")"
quality="$(curl -fsS "${auth[@]}" "$base/knowledge-quality")"
python3 -c 'import json,sys; d=json.load(sys.stdin)["data"]; assert d["quality_score"]==100 and d["issues_count"]==0' <<<"$quality"
evaluation="$(curl -fsS -X POST "${auth[@]}" -H 'Content-Type: application/json' --data '{"question":"What is the reimbursement deadline?","expected_keyword":"25"}' "$base/rag-evaluations/run")"
python3 -c 'import json,sys; d=json.load(sys.stdin)["data"]; assert d["passed"] and d["citations_count"]>=1' <<<"$evaluation"
robot="$(curl -fsS -X POST "${auth[@]}" -H 'Content-Type: application/json' --data '{"name":"Acceptance Robot","webhook_url":"https://qyapi.weixin.qq.com/cgi-bin/webhook/send?key=acceptance-placeholder","enabled":true}' "$base/wecom-configs")"
robot_id="$(python3 -c 'import json,sys; d=json.load(sys.stdin)["data"]; assert d["webhook_configured"] and "webhook_url" not in d; print(d["id"])' <<<"$robot")"
curl -fsS -X POST "${auth[@]}" -H 'Content-Type: application/json' --data '{"send_test_message":false}' "$base/wecom-configs/$robot_id/test" | python3 -c 'import json,sys; d=json.load(sys.stdin); assert d["ok"] and d["mode"]=="validation"'

unknown_body='{"question":"quantum entanglement laboratory conclusion"}'
unknown="$(curl -fsS -X POST "${auth[@]}" -H 'Content-Type: application/json' --data "$unknown_body" "$base/knowledge-chat/ask")"
python3 -c 'import json,sys; d=json.load(sys.stdin); assert len(d["citations"])==0 and d["provider"]=="knowledge-guard"' <<<"$unknown"
curl -fsS -X POST "${auth[@]}" -H 'Content-Type: application/json' --data "{\"message_id\":\"$message_id\",\"rating\":\"up\"}" "$base/knowledge-chat/feedback" >/dev/null
curl -fsS -X DELETE "${auth[@]}" "$base/documents/$document_id" >/dev/null

echo '{"passed":true,"platform":"linux","commercial_runtime_used":false}'
