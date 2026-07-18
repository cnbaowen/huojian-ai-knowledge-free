#!/usr/bin/env bash
set -euo pipefail

root="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$root"

version="${1:-$(git describe --tags --exact-match HEAD 2>/dev/null || true)}"
[[ -n "$version" ]] || { echo 'version or exact Git tag is required' >&2; exit 1; }
[[ -z "$(git status --porcelain)" ]] || { echo 'working tree must be clean' >&2; exit 1; }

bash scripts/release-check.sh
mkdir -p output/release
archive="output/release/huojian-ai-knowledge-free-${version}.zip"
git archive --format=zip --prefix="huojian-ai-knowledge-free-${version}/" -o "$archive" HEAD
(
  cd output/release
  sha256sum "$(basename "$archive")" > SHA256SUMS.txt
)
echo "$archive"
