<!--
目的：「Webinar レコードの形と状態」の明文化
-->

# S2J Webinar Service - レコード仕様

本ドキュメントは、計算の入出力となる **Webinar レコード** と、その **status** を定義します。

## 責務

* レコードフィールドの意味を定義すること。
* `not_created` / `synced` / `dirty` / `error` の意味を定義すること。

## 非責務

* 検証条件の列挙本体 ([validation_spec.md](./validation_spec.md))
* Zoom パス文字列の組立 ([request_spec.md](./request_spec.md))
* イベントメタのキー名、およびメタ ↔ 本レコードの対応表 (いずれも [S2J Webinar](https://github.com/stein2nd/s2j-webinar) のプラグイン仕様。本ライブラリは `WebinarRecord` のキーだけを正とする)
* `dirty` / `synced` の判定と、前回送ったスナップショットの保持 (呼び出し側)

## レコードの形

`start_url` は、レコードに持ちません (保存しない)。

```text
provider              接続先名。初版の実装は zoom。差し替え可能
topic                 タイトル。空は不足。200文字まで
agenda                概要。空可。空ならリクエストに載せない。2000文字まで
start_at              タイムゾーン付きの開始瞬間 (例: 2026-10-09T15:00:00+09:00)
timezone              IANA
duration_minutes      所要時間 (分)。1以上
auto_recording        none | cloud | local。欠落時のデフォルトは cloud
approval_type         0 | 1 | 2。欠落時のデフォルトは0 (必須・自動承認)
panelists             順序あり。各要素は name と email。1人目が質問メール送信先
webinar_id            未作成なら空
webinar_uuid          空可
join_url              空可
status                not_created | synced | dirty | error
last_error            空、または直近の失敗文。トークンは入れない
```

## status

**判定とスナップショット保持は、常に呼び出し側**です。本ライブラリは受け取った `status` とレコード内容だけを使い、`dirty` / `synced` を推測・再計算しません。操作計画は渡された値を正とします ([operation_spec.md](./operation_spec.md))。

| status | 意味 |
| --- | --- |
| `not_created` | `webinar_id` が空で、作成失敗の痕跡がない |
| `synced` | 直近の作成または更新が成功し、その際送った本体項目から呼び出し側が変えていない |
| `dirty` | 成功のあと、**本体項目** (topic / 日時 / 録画 / 参加登録等) が変わった。まだ Zoom に送っていない |
| `error` | 直近の API が失敗した。作成前なら `webinar_id` は空のまま (`last_error` 非空)

**登壇者だけ・アンケートだけの変更は、`synced` のままでよいです。** 操作計画の synced 分岐が Panelist 差分と `attach_survey` を扱います ([operation_spec.md](./operation_spec.md))。

## 正規化方針

* `provider` 欠落時は `zoom` でよい (初版)。未知の provider は検証で `provider_unsupported` ([validation_spec.md](./validation_spec.md))。例外にしない。レジストリの正本は [provider_spec.md](./provider_spec.md)。空実装は置かない。
* `topic` 欠落 → `""` (検証で `topic_empty`)
* `start_at` 欠落 → `""` (検証で `start_at_invalid`)
* `timezone` 欠落 → `""` (検証で `timezone_invalid`)
* `duration_minutes` 欠落 → `0` (検証で `duration_invalid`。1以上が必要)
* `auto_recording` 欠落 → `cloud`
* `approval_type` 欠落 → `0`
* `agenda` 欠落 → `""`
* `panelists` 欠落 → `[]` (検証で1人以上を要求)
* **`panelists[].email` の大小はレコード正規化では書き換えない** (表示用の大文字を残す)。メールの trim + ASCII 小文字は差分と比較・リクエスト組立だけで行う ([panelist_spec.md](./panelist_spec.md))
* `webinar_id` / `webinar_uuid` / `join_url` / `last_error` 欠落 → `""`
* **`webinar_id` が空** の場合:
  * `last_error` が非空、または入力 `status` が `error` → **`error` を維持** (作成失敗を UI に残す)
  * 上記以外 (欠落、`synced` / `dirty` の矛盾を含む) → **`not_created`**
* **`webinar_id` が非空で `status` が欠落 → `dirty`** (再送安全側。呼び出し側が明示した synced 等は維持)
* 未知のキーは無視してよい (前方互換)

## 関連

* 検証: [validation_spec.md](./validation_spec.md)
* 契約: [../contracts/data_contract_spec.md](../contracts/data_contract_spec.md)
