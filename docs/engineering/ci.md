<!--
目的：「CI 品質ゲート」の明文化
-->

# S2J Webinar Service - CI

## 品質ゲート方針

* 品質ゲートは CI で自動実行し、失敗時は merge 不可とする。
* 外部プロバイダ (Zoom) へのライブ呼び出しは CI に含めない。

## CI マトリックス (初版想定)

WordPress 統合ジョブは、本ライブラリ単体では持ちません。

| ジョブ | 内容 |
| --- | --- |
| php-quality | `composer install` → PHPUnit → PHPStan → PHPCS |
| docs-lint | `README.md` / `CHANGELOG.md` / `docs/**` / `docs_mod/**` 変更時に `npm run lint:docs` |

## 関連

* テスト: [../testing.md](../testing.md)
* リリース: [build_and_release.md](./build_and_release.md)
