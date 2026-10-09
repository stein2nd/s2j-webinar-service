# S2J Webinar Service - サービス仕様 (統合見取り図)

統合見取り図です。検査・操作・写像の **規則の正本は常に `core/`** です。本書の表は要約であり、食い違う場合は `core/` を直し、本書を追随させます。契約は `contracts/`、公開面は `interfaces/` です。索引は [specs.md](./specs.md) です。

記録日は2026-10-03、分割起草は2026-10-09です。

## 概要

本ライブラリは、GatherPress のイベントから Zoom Webinar を作成・更新・削除・再取得するためのリクエスト材料を組み立てます。**WordPress 非依存** です。HTTP の実行、OAuth トークン保存、画面は [S2J Webinar](https://github.com/stein2nd/s2j-webinar) です。設問の検査は [S2J Webinar Survey Service](https://github.com/stein2nd/s2j-webinar-survey-service) です。本ライブラリは `ready` 文書の Zoom アンケート写像だけを持ちます。

プラグイン仕様: [s2j-webinar docs_mod/specs.md](https://github.com/stein2nd/s2j-webinar/blob/main/docs_mod/specs.md) (プラグインが `docs/` に移行したら追随する)。

| 層 | 名称 | 役割 |
| --- | --- | --- |
| イベント UI | [stein2nd/gatherpress](https://github.com/stein2nd/gatherpress) | イベント画面。S2J コードは置かない |
| 呼び出し側 | [S2J Webinar](https://github.com/stein2nd/s2j-webinar) | OAuth、HTTP、メタ、画面 |
| 計算 | **本ライブラリ** | 検証、操作計画、リクエスト材料、写像 |
| 設問検査 | [Survey Service](https://github.com/stein2nd/s2j-webinar-survey-service) | 不足・助言。本ライブラリは写像のみ |

`kis-event-manager` の置き換え先は、この組み合わせです。Meeting は作りません。

## 目的 (初版)

* 接続済み Zoom ユーザーの Webinar 作成・更新・削除・再取得のリクエスト材料
* 複数 Panelist の追加・削除材料。質問メール送信先は1人目の氏名と社内メール
* 録画方式 `none` / `cloud` / `local` (デフォルト `cloud`)。ファイル転送なし
* 結果として Webinar ID、UUID、`join_url`、`status` を返す
* `ready` 設問文書の survey PATCH 写像

## 非目標 (初版)

* 出席・録画ファイル・効果測定の取り込み、registrant 自動登録
* Zoom 手修正の検知取り込み、Meeting
* 他会議プロダクトの記述子追加 (初版以降。レジストリの差し替え口は残す)
* CoverArt / QR / フライヤー (プラグイン後続)、GatherPress フォーク改変、WordPress.org 掲載
* 設問の検査・助言 (Survey Service)

## 本ライブラリの責務 (要約)

正本は分割仕様です。

| 責務 | 正本 |
| --- | --- |
| レコードと status | [core/record_spec.md](./core/record_spec.md) |
| 検証 | [core/validation_spec.md](./core/validation_spec.md) |
| プロバイダ・レジストリ | [core/provider_spec.md](./core/provider_spec.md) |
| 操作計画 | [core/operation_spec.md](./core/operation_spec.md) |
| Panelist 差分 | [core/panelist_spec.md](./core/panelist_spec.md) |
| リクエスト材料 | [core/request_spec.md](./core/request_spec.md) |
| 結果写像 | [core/result_spec.md](./core/result_spec.md) |
| OAuth 材料 | [core/oauth_spec.md](./core/oauth_spec.md) |
| アンケート写像 | [core/survey_map_spec.md](./core/survey_map_spec.md) |

入力は Webinar レコード (と操作コンテキスト) です。出力は不足コード、操作列、リクエスト材料、更新後レコードです。`Authorization` は材料に含めません。

## Zoom API (初版)

新規登録は、二段階です: 作成応答の `id` がウェビナー ID → 続けて Panelist (と必要なら survey)。発行 webhook は待ちません。追加失敗でも Webinar は削除しません。

`start_url` は、保存しません。「Zoom で開く」は `get` のあと `map_webinar_response` が返す揮発 `start_url` をその場で開きます。

スコープは、本人用グラニュラーのみです ([oauth_spec.md](./core/oauth_spec.md))。

| 操作 | メソッド |
| --- | --- |
| 作成 | `POST /users/me/webinars` |
| 取得 / 更新 / 削除 | `GET` / `PATCH` / `DELETE /webinars/{webinarId}` |
| Panelist 追加 | `POST /webinars/{webinarId}/panelists` |
| Panelist 削除 | `DELETE /webinars/{webinarId}/panelists/{panelistId}` (メール。全員削除は使わない) |
| アンケート | `PATCH /webinars/{webinarId}/survey` |

## 作成で送る項目 (要約)

正本は [request_spec.md](./core/request_spec.md) です。下記は要点のみです。

* `topic` / `agenda`(空なら送らない) / `start_time` / `timezone` / `duration` / `type`=5
* `auto_recording`、`audio`=voip、カメラと HD は false、`meeting_authentication`=false
* Q&A 一式 (`enable` / `allow_anonymous_questions` / `answer_questions`=`only`)
* `contact_*` は1人目、`approval_type` は省略しない (デフォルト `0`)
* パスコード・チャットデフォルト対象・継承7項目の WordPress 保持はしない

## プラグイン境界 (要約)

詳細はプラグイン仕様を参照してください。ここは境界だけです。

* 接続、HTTP、メタ、画面、開催 webhook、削除はパネル明示のみ
* **メタキー名とメタ ↔ WebinarRecord の対応表はプラグイン仕様に置く。** 本ライブラリはレコードのキーだけを知り、WP キー名を仕様に書かない
* 日時は GatherPress の start/end/timezone からプラグインがレコードに渡す。所要時間はその差 (分)。終日は開始 `00:00` と暦日数×1440分
* 公開参加 URL は `Event::set_online` 等でプラグインが書く
* CoverArt / QR / フライヤーは後続
* `plan` をいつ呼ぶか (例: ユーザーの同期操作時) はプラグインが決める

## 設計方針

FOP + Clean Coding です。パッケージ名 `s2j/webinar-service`、名前空間 `S2J\WebinarService\`、GPL-3.0-or-later です。プロバイダは記述子 + Adapter 関数のレジストリで差し替えます ([provider_spec.md](./core/provider_spec.md))。初版の実装は `zoom` だけです。空実装は置きません。管理画面での選択はプラグイン側です。

## 採用した方針 (要約)

* GatherPress イベントから Zoom Webinar に一方向。計算は本ライブラリ、I/O はプラグイン、画面は GatherPress。
* ホストは OAuth 接続ユーザー。質問メールは登壇者1人目。録画デフォルト cloud。参加登録デフォルト `0`。
* Panelist 削除はメール単位。並び替えだけでは削除しない。氏名のみ変更は削除+追加。
* `dirty` の場合だけ update。再取得は表示用でイベント項目を上書きせず、`status` も変えない。dirty/synced の判定は呼び出し側。
* アンケート写像は本ライブラリ。検査は Survey Service ([docs/specs.md](https://github.com/stein2nd/s2j-webinar-survey-service/blob/main/docs/specs.md))。添付入口は `attach_survey` (survey 未完了を create の条件にしない。後から同じ op で付ける)。

## 実装順

1. スケルトンと純関数の初版 (PHPUnit、WP なし、HTTP なし)。
2. プラグインが Composer require し、OAuth と作成・保存をつなぐ。
3. 更新・削除・再取得、および Panelist、survey 写像を足す。
4. CoverArt / QR 等はプラグイン後続。

## 改訂履歴

| 日付 | 内容 |
| --- | --- |
| 2026-10-03 | 初版ドラフト。GatherPress をイベント UI、本ライブラリをリクエスト組立、と記録 |
| 2026-10-03 | 質問メール送信先を登壇者1人目の `contact_*` に改めた、と記録 |
| 2026-10-03 | 呼び出し側プラグイン s2j-webinar のリポジトリ、と記録 |
| 2026-10-05 | 参加登録・ゴミ箱非削除、Q&A 送らない案、QR utm、プロバイダ、OAuth、公開 URL、開催 webhook、日時・終日、Panelist 削除、start_url、継承7項目、と記録 |
| 2026-10-05 | 参加登録デフォルトをいったん `2` と記録 (後続で必須・自動承認に変更) |
| 2026-10-06 | 新規登録は二段階、スケジュール画面項目表、参加登録デフォルトを必須・自動承認 (`0`)、録画デフォルト cloud、アンケート検査を Survey Service に分離、と記録 |
| 2026-10-07 | Q&A 一式送信、HD false、チャット非送信、meeting_authentication false、アンケート写像と type 確定、と記録 |
| 2026-10-09 | Similarity / Survey Service に倣い仕様を分割。規則正本は `core/`。Survey リンクを `docs/specs.md` に更新。参加登録デフォルトは `0` で統一、と記録 |
| 2026-10-09 | 合意済み仕様キットを `docs_mod/` から `docs/` に移行、と記録 |
| 2026-10-09 | 監査 BP を反映: survey 入口一本化、context / operations 形、diff 主体、dirty 所有、provider 不足、get 後 status、duration ≥1、start_at 例、status 正規化、と記録 |
| 2026-10-09 | 再監査 BP: attach_survey の step 必須、`webinar:update:survey`、単一 plan 図、成功は2xx、`none` 廃止、dirty は本体のみ、get/delete 失敗写像、と記録 |
| 2026-10-09 | 再々監査 BP: id 空の error 維持、失敗で列中断、survey ボディ包み、列順、intend_retry 注記、OAuth scope 一括、と記録 |
| 2026-10-09 | 軽微 BP: 手順3見出し、usage break、204/null、testing 列順、internal_name 非送、と記録 |
| 2026-10-09 | 境界 BP: メタキーはプラグインのみ、Panelist ボディ形固定、メールは trim+ASCII 小文字、と記録 |
| 2026-10-09 | メール正規化は差分・リクエストのみ。レコード normalize では大小を書き戻さない、と記録 |
| 2026-10-09 | プロバイダはアダプタで差し替え可能とする (のち記述子 + Adapter 関数と明文化)。初版は `zoom` のみ。空実装は置かない。管理画面の選択はプラグイン側、と記録 |
| 2026-10-09 | プロバイダ・レジストリ仕様を追加 ([provider_spec.md](./core/provider_spec.md)。Slug Generater の記述子形に倣う)、と記録 |
