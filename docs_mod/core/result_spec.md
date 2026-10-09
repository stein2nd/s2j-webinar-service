<!--
目的：「API 応答からレコードへの写像」の明文化
-->

# S2J Webinar Service - 結果写像の仕様

本ファイルは **Zoom (`zoom`) 記述子** の `map_result` 正本です。公開入口は `map_webinar_response` ([../interfaces/php_api_spec.md](../interfaces/php_api_spec.md))。レジストリは [provider_spec.md](./provider_spec.md)。

## 責務

* 渡された HTTP ステータスとボディから、更新後レコードを作ること。
* トークンとクライアント・シークレットを結果に残さないこと。

## 非責務

* HTTP の実行
* リトライ (必要ならプラグインが再 `plan`)
* `start_url` の永続化 (保存しない。プラグインが「Zoom で開く」でその場だけ使う)
* `dirty` / `synced` の業務判定 (呼び出し側。本写像は成功・失敗に応じた status だけを書く)

## 成功・失敗の判定

* **成功:** HTTP ステータスが `2xx` (create の `201` を含む)
* **失敗:** それ以外、または (create のように id が必要なのに) ボディが解釈不能
* update / delete / panelist / survey の成功は **`204` かつ body null** がありうる。その場合も成功写像を適用する
* create 成功は body から `id` 等を読む
* 本ライブラリはリトライしない

## create 成功

発行の webhook (`webinar.created`) は待ちません。

* `webinar_id` ← 応答の `id`
* `webinar_uuid` ← 応答にあれば埋める
* `join_url` ← 応答の `join_url`
* `status` ← `synced` (続く Panelist / survey の成否は、それぞれの写像で更新する)
* `last_error` ← `""`

## create / update / panelist / survey 失敗

* `status` ← `error`
* `last_error` ← ユーザー向けに使える短い文 (ボディの summary 等)。トークンを含めない
* 作成前の失敗では `webinar_id` を空のままにする
* 作成後の追加失敗では `webinar_id` は残す (Webinar を削除しない)

## update 成功

* 送った項目に対応するレコードを維持し `status` ← `synced`、`last_error` ← `""`

## add_panelists / remove_panelists / attach_survey 成功

* `last_error` ← `""`
* `status` ← `synced` (本体がすでに存在する前提の成功。直前が `error` でも、この操作が成功したら `synced` にしてよい)
* `webinar_id` / `webinar_uuid` / `join_url` は変えない

## delete 成功

* `webinar_id` / `webinar_uuid` / `join_url` を空にし、`status` ← `not_created`、`last_error` ← `""`

## delete 失敗

* `status` ← `error`
* `last_error` ← 短い文 (トークンを含めない)
* `webinar_id` / `webinar_uuid` / `join_url` は残す (Zoom 上はまだ消えていない前提)

## get 成功

* 表示用に `join_url` 等を更新してよい
* **`status` は変えない**
* **イベントの `topic` / `start_at` / `duration_minutes` を Zoom の値で上書きしない**
* `start_url` は **`map_result` / 公開 `map_webinar_response` の戻り** に載せる (揮発。レコード正本には書かない。ファサードが HTTP ボディから別途抜かない)
* `last_error` は変えない (表示用 get で消さない)

## get 失敗

* **`status` は変えない** (表示用。本体の同期状態は維持)
* `last_error` に短い文を入れてよい
* レコード正本の `topic` / `start_at` / `duration_minutes` は変えない

## 関連

* レコード: [record_spec.md](./record_spec.md)
* 契約: [../contracts/data_contract_spec.md](../contracts/data_contract_spec.md)
