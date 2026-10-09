<!--
目的：「用語、型、制約」の明文化
-->

# S2J Webinar Service - データ辞書

## 非対象

* WordPress / GatherPress のメタキー名、およびメタ ↔ WebinarRecord の対応表 (プラグイン仕様。本辞書はレコード・計算用語のみ)
* Survey の不足・助言コード (Survey Service)
* UI のラベル文言 (プラグイン)

## 用語

### WebinarRecord

そのイベントに紐づく Webinar の計算用レコードです。形は [../core/record_spec.md](../core/record_spec.md)。

### provider

レコード上の接続先 ID (文字列)。形の正本は [../core/provider_spec.md](../core/provider_spec.md) です。初版の実装は `zoom` のみです。未知の値は検証・リクエスト組立・応答写像で不足 `provider_unsupported` です (例外にしない)。管理画面での選択は呼び出し側 (プラグイン) です。

| 用語 | 意味 |
| --- | --- |
| `provider` | レコード上の接続先 ID |
| 記述子 / `WebinarProvider` | レジストリの1件 (`id`、案内、Adapter 関数) |
| Adapter (記述子上) | `build_request` / `map_result` / `oauth_materials` などの写像関数 |
| HTTP Adapter | プラグイン側の HTTP 実行。本ライブラリの公開面には含めない |
| `provider_unsupported` | レジストリに記述子がない場合の不足コード |

### panelist (登壇者)

登壇者 (Panelist) 1人。レコードキーは `panelists`。各要素は `name` と `email`。1人目が質問メール送信先 (`contact_name` / `contact_email`)。レコード上の `email` の大小は正規化で変えない。差分・送付時だけ trim + ASCII 小文字 ([../core/panelist_spec.md](../core/panelist_spec.md))。

### auto_recording

`none` / `cloud` / `local`。欠落時デフォルトは `cloud`。ファイル転送はしません。

### approval_type

参加登録。`0` 必須・自動承認 (デフォルト) / `1` 必須・手動承認 / `2` 不要。

### status (レコード)

`not_created` / `synced` / `dirty` / `error`。[record_spec.md](../core/record_spec.md) を正本とします。判定は呼び出し側。`dirty` は本体項目の変更用。登壇者・アンケートだけなら `synced` のままでよい。作成失敗 (id 空) は `error` + `last_error` を維持しうる。Survey 文書の `status` (`draft` / `ready`) と混同しません。

### RequestMaterial

`method` / `path` / `body`。`Authorization` を含みません。

### Deficiency / deficiency code

未知 `provider` はレジストリ lookup または公開ファサードが `deficiencies` に載せます。記述子の `build_request` は `RequestMaterial` のみを返し、Deficiency は返しません ([../core/provider_spec.md](../core/provider_spec.md))。

| 用語 | 意味 |
| --- | --- |
| Deficiency | **不足コード文字列1つ** (例: `provider_unsupported`)。一覧は [../core/validation_spec.md](../core/validation_spec.md) |
| `deficiencies` | 公開封筒の `string[]` (`validate` / `build_webinar_request` / `map_webinar_response`) |
| deficiency code | 検証・材料組立・応答写像で使うコード名。Deficiency と同義 |

### answer_kind vs Zoom type

Survey 文書の `answer_kind` (`short` 等) と Zoom の `type` (`short_answer` 等) を混同しません。写像は [../core/survey_map_spec.md](../core/survey_map_spec.md)。

## 一貫性ルール

* コード名は snake_case の英数字である。
* プラグインの表示文はコードから導く。コード文字列を画面に出さない。
* 仕様文では「日本語を表示」ではなく「適切なメッセージ文を表示」と書く (プラグイン側 i18n)。
