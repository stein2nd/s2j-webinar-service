#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "$0")/../.." && pwd)"
cd "$ROOT_DIR"

echo "==> [verify] Composer validate (Packagist-ready)"
composer validate-composer

echo "==> [verify] Composer archive dist"
composer verify-dist

echo "==> [verify] PHPUnit"
composer test

echo "==> [verify] PHPStan"
composer phpstan

echo "==> [verify] PHPCS"
composer phpcs

echo "==> [verify] Done"
