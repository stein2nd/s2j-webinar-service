# S2J Webinar Service - CHANGELOG

## unreleased

## 0.0.1 - 2026-10-08

### Changed

* `docs_mod/service_spec.md` の表記をドキュメント lint に合わせた (`下記`、`場合`、`際`、`デフォルト`)

## 0.0.1 - 2026-10-07

### Changed

* セッション中の Q&A は作成で `settings.question_and_answer` を一式で送る。`enable` = `true`、`allow_anonymous_questions` = `true`、`answer_questions` = `only`。コメントと upvote は送らない
* HD は `settings.hd_video` = `false`。出席者の参加時認証は `settings.meeting_authentication` = `false`
* チャットのデフォルト対象は送らない。作成 API に一対一のフィールドがない
* `panelist_authentication` と `enforce_login` は使わない
* アンケート添付は `PATCH /webinars/{webinarId}/survey` の材料に写す。回答レポートの GET は使わない
* 初版の設問型は `single` / `multiple` / `short_answer` / `long_answer` / `rating_scale`。`prompt` は `name`、`required` は `answer_required`、選択肢は `answers`
* `matching`、`rank_order`、`fill_in_the_blank`、画像、スキップロジックは送らない

## 0.0.1 - 2026-10-06

### Changed

* `docs_mod/service_spec.md` のレコード項目を表にし、表記をドキュメント lint に合わせた
* 新規登録は二段階。ウェビナー ID は作成応答の `id`。発行は購読しない。その ID でタブの初期値を追加する
* スケジュール画面の項目を確定。参加登録のデフォルトは必須・自動承認 (`approval_type` = `0`)。録画のデフォルトは `cloud`
* トピックは200文字、説明は2000文字。パスコードは送らない。オーディオは `voip`。ホストとパネリストのカメラは `false`
* セッション中の Q&A は、使う、匿名可、回答済みだけ、を一式で送る。公式フィールド名が確定してから足す
* アンケート設問の検査は S2J Webinar Survey Service に分ける

## 0.0.1 - 2026-10-05

### Changed

* `docs_mod/service_spec.md` の未決を決定に更新
    * Panelist の削除は `DELETE /webinars/{webinarId}/panelists/{panelistId}`。`panelistId` はメール。全員削除は使わない
    * 参加登録はイベントごと。デフォルトは不要 (`approval_type` = `2`)。選んだ値は省略せず送る
    * セッション中の Q&A は初版では送らない。作成でアカウント設定を継ぐのは、Zoom が公式に挙げた7項目だけ
    * 開始 URL は保存しない。「Zoom で開く」は押した際に取得する。通常ユーザーの期限は2時間で、タイマーにはしない
    * OAuth はユーザー管理アプリ。スコープは本人用のグラニュラーだけ。プロバイダはコードで `zoom` と渡す
    * 日時は GatherPress の開始・終了・タイムゾーンから渡す。所要時間はその差 (分)。終日は開始を0時とし、所要時間は暦日数×1440分
    * 開始と終了は `webinar.started` と `webinar.ended` で受ける。ゴミ箱と完全削除では Zoom を削除しない

## 0.0.1 - 2026-10-04

### Changed

* ドキュメント lint の `@s2j/docs-linter` を ^1.0.27に更新
* `docs_mod/service_spec.md` の表記をドキュメント lint に合わせた (`できない`、`デフォルト`、`ユーザー`)

## 0.0.1 - 2026-10-03

### Added

* 確定前のサービス仕様を `docs_mod/service_spec.md` に記載
* ドキュメント lint を追加 (`@s2j/docs-linter` ^1.0.26、`npm run lint:docs`、GitHub Actions)
* npm v12以降向けに `.npmrc` の `allow-git=all` と `package.json` の `allowScripts` を追加

### Changed

* README に、本ライブラリの役割と仕様ドラフトへの参照を記載
* `.gitignore` を Composer、Node、テスト成果物向けに拡張
* `.gitattributes` で開発専用パスを Composer 配布物から除外
* `.vscode/settings.json` で `json.schemaDownload.enable` と textlint の保存時修正を有効化
