<!--
目的：「Webinar レコードの検証」の明文化
-->

# S2J Webinar Service - 検証仕様

本ドキュメントは、操作計画の前に満たすべき **不足** の規則を定義します。

## 責務

* 不足コードとその条件を定義すること。
* 不足がある場合、**呼び出し側は書き込み用の `plan` を呼ばない**こと (表示用 get は `intend_get` + id 非空で可)。`plan` は再検証しない。

## 非責務

* 表示用のメッセージ文 (プラグイン。国際化関数を経由)
* HTTP ステータスの解釈 ([result_spec.md](./result_spec.md))

## 不足コード

| コード | 条件 |
| --- | --- |
| `topic_empty` | `topic` が空 (trim 後) |
| `topic_too_long` | `topic` が200文字超 |
| `agenda_too_long` | `agenda` が2000文字超 |
| `start_at_invalid` | 開始が解釈できない、またはタイムゾーン付き瞬間として不正 |
| `timezone_invalid` | `timezone` が空、または不正 |
| `duration_invalid` | `duration_minutes` が数値でない、または **1未満** |
| `auto_recording_invalid` | `none` / `cloud` / `local` のいずれでもない (欠落は正規化で `cloud`) |
| `approval_type_invalid` | `0` / `1` / `2` のいずれでもない (欠落は正規化で `0`) |
| `panelists_empty` | 登壇者が0件 |
| `panelist_incomplete` | いずれかの登壇者で `name` または `email` が空 (trim 後。レコードには小文字化しない) |
| `panelist_email_invalid` | いずれかの登壇者の `email` がメールとして不正 (trim 後。大小は問わない) |
| `provider_unsupported` | `provider` にレジストリの記述子がない (初版は `zoom` のみ)。**例外にせず不足で返す** (`validate`、`build_webinar_request`、`map_webinar_response`) |

## 適用

1. 正規化 ([record_spec.md](./record_spec.md)) のあと検証する。
2. 不足が1件以上 → **呼び出し側**は書き込み用の `plan` を呼ばない。表示用 get だけなら `intend_get` + `webinar_id` 非空で `plan` してよい。`plan` 自体は再検証しない ([operation_spec.md](./operation_spec.md))。
3. コードの列は安定した順 (上記表の順、登壇者は入力順) で返す。

## 質問メール送信先

独立フィールドはありません。1人目の `name` / `email` を `settings.contact_name` / `settings.contact_email` に載せる前提であり、上記の登壇者検証で足ります。

## 関連

* レコード: [record_spec.md](./record_spec.md)
* 公開結果形: [../contracts/data_contract_spec.md](../contracts/data_contract_spec.md)
