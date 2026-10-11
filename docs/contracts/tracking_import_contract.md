<!--
目的：「取り込み DTO の契約」
-->

# Source Tracking 取り込み — 入出力契約

写像規則の正本は [../core/tracking_import_spec.md](../core/tracking_import_spec.md) です。Webinar レコード DTO は [data_contract_spec.md](./data_contract_spec.md) です。

## TrackingSourceItem (正規化行)

| フィールド | 型 | 必須 | 説明 |
| --- | --- | --- | --- |
| `source_name` | string | はい | 接続先ソース名。Source QR は `id` との完全一致で自動割り当て |
| `registration_url` | string | はい | 登録用 URL。QR 埋め込み文字列 |
| `external_id` | string | いいえ | 接続先側 ID (Zoom `id`) |

## build 結果

[RequestMaterial](../core/request_spec.md) と同型:

| フィールド | 型 |
| --- | --- |
| `method` | `GET` (初版 zoom のみ) |
| `path` | string |
| `body` | `null` |

公開封筒: `{ material?, deficiencies: list<string> }`

## map 結果

| フィールド | 型 | 説明 |
| --- | --- | --- |
| `items` | `TrackingSourceItem[]` | 有効行のみ |
| `deficiencies` | `string[]` | tracking 専用コード。空なら `items` を信頼してよい |

WebinarRecord は **更新しない**。

## 改訂履歴

| 日付 | 内容 |
| --- | --- |
| 2026-10-11 | v0.0.2 `zoom` GET 取り込み DTO を `docs/` に確定 |
