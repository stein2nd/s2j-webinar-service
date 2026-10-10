<!--
目的：「テスト戦略」の明文化
-->

# S2J Webinar Service - テスト戦略

## 設計意図 (ゴール)

WordPress も Zoom ライブも使わずに、検証・操作計画・差分・写像の分岐を固定します。

## テストレベル

| レベル | 内容 | 環境 |
| --- | --- | --- |
| Unit | 公開関数の純関数 | PHPUnit、WP なし |
| 契約スナップショット (任意) | 代表的なリクエスト JSON | PHPUnit |
| プラグイン結合 | OAuth、HTTP、メタ | プラグイン repo |

## 必須カバレッジ (規則)

* 不足コードは、各コードについて真になる入力を1件以上 (`duration_invalid` は0分、`provider_unsupported` は非 zoom)。`build_webinar_request` と `map_webinar_response` の未知 provider も同コード
* `attach_survey` で `survey_document` 欠落の build 直呼びは `InvalidArgumentException`
* id 非空前提 op (`update` 等) で `webinar_id` 空の build 直呼びは `InvalidArgumentException` (不足コードにしない)
* `topic` / `start_at` / `timezone` 欠落→`""`、`duration_minutes` 欠落→`0`。`auto_recording` / `approval_type` のデフォルト埋め。id 空 + last_error →`error` 維持、id 空のみ→`not_created`、id 非空で status 欠落→`dirty`
* `plan` は再 validate しない。不足ゼロ前提の入力で分岐を固定する
* `dirty` の場合だけ `update`、`synced` では無条件 update なし。列順: create|update → remove → add → attach_survey → get。`dirty` / create でも `intend_get` なら末尾に `get` (`delete` 単独は付けない)
* `survey_document` 非空で `attach_survey` が計画に載り、要素に `survey_document` が必須。ボディは `custom_survey` 包み。create のみ (文書なし) でも計画できる。SoT 入口は `build_webinar_request( 'attach_survey' )`
* `map` の未知 `op` は `InvalidArgumentException`。`map_result` 戻りは `{ record, start_url? }`
* 何もしない場合は空配列 (`none` を返さない)
* Panelist: 追加・削除・氏名のみ変更 (削除+追加)、並び替えだけでは差分なし。`remove_panelists` は1メール=1要素
* Panelist メール: 差分・リクエストでは trim + ASCII 小文字 (`Taro@…` と `taro@…` は同一)。`normalize` 後のレコードは入力の大小を維持。add ボディは小文字化した `{ "panelists": [ { "name", "email" } ] }` スナップショット1件以上
* id 非空の `error` で `intend_retry` なしは空列 (または get のみ)。`intend_retry` 真かつ id 非空は手順3と同じ列 (`update` → 差分 → `attach_survey` → 任意 `get`)。id 空の `error` は create 計画可
* create 成功写像で `webinar_id` / `join_url`、失敗で `error` かつ作成前は ID 空。2xx 以外は失敗
* panelist / survey 成功で `synced` と `last_error` 空。get 成功・失敗とも status 非変更。delete 失敗は error かつ id 残す
* survey 写像: 5種の type、`short`→`short_answer` 等。送らない type を載せない
* OAuth 材料にシークレットを結果ログ用に複製しない

## 外部依存

* Zoom 応答はフィクスチャ JSON で与える。ライブ呼び出しはしない。

## カバレッジ生成物

* `/coverage/` (gitignore)。仕様や archive に生 HTML を貼らない。

## 関連

* CI: [engineering/ci.md](./engineering/ci.md)
* ガバナンス: [governance/documentation_governance.md](./governance/documentation_governance.md)
