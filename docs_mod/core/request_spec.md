<!--
目的：「Zoom REST リクエスト材料」の明文化
-->

# S2J Webinar Service - リクエスト材料仕様

本ファイルは **Zoom (`zoom`) 記述子** の `build_request` 正本です。他プロバイダは各自の記述子と仕様を持ちます。レジストリの形は [provider_spec.md](./provider_spec.md)。公開入口は `build_webinar_request` ([../interfaces/php_api_spec.md](../interfaces/php_api_spec.md))。

## 責務

* 操作ごとのメソッド・パス・ボディを定義すること。
* `Authorization` ヘッダーを材料に含めないこと (プラグインがトークンを付ける)。

## 非責務

* HTTP の実行
* OAuth トークンの取得

## 共通

戻り形:

```text
method   GET | POST | PATCH | DELETE
path     / から始まるパス (ホスト名なし)
body     object または null (GET/DELETE は null でよい)
```

Webinar の `type` は単発の `5` だけです。繰り返し (`6` / `9`) は使いません。Meeting は作りません。

## create (`POST /users/me/webinars`)

初版はユーザー向け OAuth の **`me` 固定** です (`{userId}` プレースホルダは使わない)。

| 項目 | 送り先 |
| --- | --- |
| タイトル | `topic`。200文字まで |
| 概要 | `agenda`。空ならキーを送らない。2000文字まで |
| 開始日時 | `start_time`。レコードの `start_at` (例: `2026-10-09T15:00:00+09:00`) から、Zoom が受けるローカル壁時計形 (例: `2026-10-09T15:00:00`) に落とし、オフセットは `timezone` に任せる |
| タイムゾーン | `timezone` (IANA。例: `Asia/Tokyo`) |
| 所要時間 (分) | `duration` (レコードの `duration_minutes`。1以上) |
| 種別 | `type` = `5` |
| 録画 | `settings.auto_recording` |
| オーディオ | `settings.audio` = `voip` |
| ホストのカメラ | `settings.host_video` = `false` |
| パネリストのカメラ | `settings.panelists_video` = `false` |
| HD | `settings.hd_video` = `false` |
| 出席者の参加時認証 | `settings.meeting_authentication` = `false`。`authentication_option` / `authentication_domains` は送らない |
| セッション中の Q&A | `settings.question_and_answer`: `enable` = `true`、`allow_anonymous_questions` = `true`、`answer_questions` = `only`。`enable` だけでは送らない。コメントと upvote は送らない |
| 質問メール送信先 | `settings.contact_name` / `settings.contact_email` (1人目。`email` は [panelist_spec.md](./panelist_spec.md) の正規化後。レコード上の大小は変えない) |
| 参加登録 | `settings.approval_type`。省略しない |

### 送らないもの (初版)

* `password` (パスコード)。公開ページにも出さない
* `panelist_authentication`、`enforce_login`
* 代替ホスト、オンデマンド、国・地域制限
* チャットのデフォルト対象 (Create API に一対一フィールドがない)
* 待機室アップロード、投票、テンプレート、ブランディング
* Zoom が「省略時アカウント継承」と公式にした7項目 (`password`、`add_watermark`、`add_audio_watermark`、`language_interpretation`、`sign_language_interpretation`、`panelist_authentication`、`allow_host_control_participant_mute_state`) は WordPress に持たず送らない

## update (`PATCH /webinars/{webinarId}`)

本ライブラリが持つ項目だけを送ります。設定の塊を部分的に送って「知らないキーを消す」ことはしません。連絡先は1人目から毎回含めてかまいません。

## delete / get

* delete: `DELETE /webinars/{webinarId}`、body なし
* get: `GET /webinars/{webinarId}`、body なし

## add_panelists

`POST /webinars/{webinarId}/panelists`

初版のボディ形は次で固定します (Zoom が受ける Panelist 追加形。操作要素の `panelists` = 差分の `add`)。キー名を推測で広げません。変更は契約更新とテスト更新を伴います。

```json
{
  "panelists": [
    { "name": "山田太郎", "email": "taro@example.co.jp" }
  ]
}
```

* `name` / `email` 以外のキーは初版では送らない。
* `email` は [panelist_spec.md](./panelist_spec.md) の正規化後の値である。

## remove_panelists

外す人ごとに1リクエスト (`operations` も1メール=1要素):

`DELETE /webinars/{webinarId}/panelists/{panelistId}`

`panelistId` は操作要素の `email` (正規化後)。body なし。

## attach_survey

公開の組立入口は `build_webinar_request( 'attach_survey', … )` です。操作要素の `survey_document` は必須 ([operation_spec.md](./operation_spec.md))。ボディのトップ形と設問写像は [survey_map_spec.md](./survey_map_spec.md) を正本とします。`build_survey_update_request` を置く場合は、同じ写像への薄い委譲とし、規則を分岐させません。

## 参加登録 (`approval_type`)

省略すると接続ユーザーのデフォルトが使われる可能性があるため、作成と更新では省略しません。

| 値 | 意味 |
| --- | --- |
| `0` | 必須・自動承認 (欠落時デフォルト) |
| `1` | 必須・手動承認 |
| `2` | 不要 (イベントページを一つの参加 URL にする回) |

## 関連

* プロバイダ・レジストリ: [provider_spec.md](./provider_spec.md)
* 操作: [operation_spec.md](./operation_spec.md)
* 公式: [Webinars](https://developers.zoom.us/docs/api/rest/reference/zoom-api/methods/#tag/Webinars)
