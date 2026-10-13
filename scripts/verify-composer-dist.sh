#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

ARCHIVE="build/s2j-webinar-service.zip"
rm -f "$ARCHIVE"
mkdir -p build

composer archive --format=zip --dir=build --file=s2j-webinar-service >/dev/null

FORBIDDEN_DIRS=(
  "tests/"
  "docs/"
  ".github/"
)

FORBIDDEN_FILES=(
  "phpunit.xml"
  "phpstan.neon"
  "phpcs.xml.dist"
)

LIST="$(unzip -l "$ARCHIVE")"
for dir in "${FORBIDDEN_DIRS[@]}"; do
  if echo "$LIST" | grep -q "${dir}"; then
    echo "composer archive must not include ${dir}" >&2
    exit 1
  fi
done

for file in "${FORBIDDEN_FILES[@]}"; do
  if echo "$LIST" | grep -qE " ${file}$"; then
    echo "composer archive must not include ${file}" >&2
    exit 1
  fi
done

if ! echo "$LIST" | grep -q "src/"; then
  echo "composer archive must include src/" >&2
  exit 1
fi

echo "composer archive dist check OK"
