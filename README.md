# S2J Webinar Service

GatherPress のイベントから Zoom Webinar を作成・更新するリクエストを組み立てる Composer ライブラリです。WordPress 非依存です。

## 要件

* **PHP 8.3以上** ([WordPress 必須要件](https://ja.wordpress.org/about/requirements/) の推奨に合わせる)

## インストール

Packagist のパッケージ名だけを require します (`repositories` に VCS / パスは書きません)。

```bash
composer require s2j/webinar-service
```

## 使い方

公開関数 (検証・操作計画・リクエスト材料・写像) の骨格は [docs/interfaces/usage_spec.md](docs/interfaces/usage_spec.md) を参照してください。

## 仕様

確定仕様の正本は [`docs/`](docs/specs.md) です。大きな改訂案は [`docs_mod/`](docs_mod/README.md) で起草します。

* 索引: [docs/specs.md](docs/specs.md)
* 統合見取り図: [docs/service_spec.md](docs/service_spec.md)
* 公開 API: [docs/interfaces/php_api_spec.md](docs/interfaces/php_api_spec.md)

## 開発

```bash
composer install
npm install
npm run quality    # PHPUnit / PHPStan / PHPCS + Composer validate / dist
npm run lint:docs
```

## パッケージ

* Composer: `s2j/webinar-service` ([Packagist](https://packagist.org/packages/s2j/webinar-service))
* ライセンス: GPL-3.0-or-later
* リリース: [docs/engineering/build_and_release.md](docs/engineering/build_and_release.md)
