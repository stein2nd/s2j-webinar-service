<!--
目的：「README と docs の整合、用語、archive」の明文化
-->

# S2J Webinar Service - ドキュメンテーション・ガバナンス

## Source of Truth

矛盾時は **core / contracts を正** とし、`service_spec.md`、README、usage を追随させます。

| 対象 | 正本 |
| --- | --- |
| 規則 | `docs_mod/core/*` (合意後は `docs/core/*`) |
| 型 | `docs_mod/contracts/*` |
| 公開 PHP API | `docs_mod/interfaces/php_api_spec.md` |
| 最短手順 | ルート `README.md` |
| 統合の見取り図 | `docs_mod/service_spec.md` (要約。規則の正本ではない) |
| 索引 | `docs_mod/specs.md` |

## 用語

* 正式なコード名は [../contracts/data_dictionary.md](../contracts/data_dictionary.md) に従う。
* Survey の `answer_kind` と Zoom の `type` を混同しない。
* 仕様文では「日本語を表示」ではなく「適切なメッセージ文を表示」と書く。
* レコードの `status` (`not_created` 等) と Survey 文書の `status` (`draft` / `ready`) を混同しない。

## Lint

* `@s2j/docs-linter` を SoT とする。
* `npm run lint:docs` の対象に `docs_mod/**/*.md` を含める。

## 分割ルール

* 新規仕様は [../specs.md](../specs.md) のレイヤーに分類する。
* Similarity の OpenAPI / SRE 層を、必要になるまで追加しない。

## 改訂フロー

* 合意後の確定正本は `docs/` である。
* 大きな改訂案は `docs_mod/` で起草し、合意のあと `docs/` に反映する。
* 依存リポジトリからのリンクは、確定後は `docs/` を指す。

## イニシアチブ証跡 (archive)

作業中三点は `docs_mod/`、完了時に archive に freeze します。機械成果物は置きません。索引は [../archive/README.md](../archive/README.md)。

| 種類 | フォルダー |
| --- | --- |
| 実装 | `docs/archive/impl-<slug>/` (三点: modification / status / test-results) |
| 改修 | `docs/archive/mod-<slug>/` |
| 仕様リライト旧正本 | `docs/archive/spec-<slug>/` |
