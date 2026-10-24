# S2J Webinar Service - CI

## 品質ゲート方針

* 品質ゲートは CI で自動実行し、失敗時は merge 不可とする。
* 外部プロバイダ (Zoom) へのライブ呼び出しは CI に含めない。

## CI マトリックス (初版想定)

WordPress 統合ジョブは、本ライブラリ単体では持ちません。

| ジョブ | 内容 |
| --- | --- |
| php-quality | PHP **8.3** — `composer install` → [`scripts/verify/all.sh`](../../scripts/verify/all.sh): validate, dist, PHPUnit, PHPStan, PHPCS |
| docs-lint | `npm run lint:docs` + PHP **8.3** で `composer validate --strict` |
| release | SemVer タグ push — PHP **8.3** で `scripts/verify/all.sh` (任意: Packagist API 更新) |

`composer.json` の `require.php` は **>=8.3** (WordPress [必須要件](https://ja.wordpress.org/about/requirements/) の推奨に合わせる)。

ローカルでは `npm run quality` が php-quality と同じ並びです。`composer install` は事前に実行してください。

## 関連

* テスト: [../testing.md](../testing.md)
* リリース: [build_and_release.md](./build_and_release.md)
