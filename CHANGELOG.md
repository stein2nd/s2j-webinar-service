# S2J Webinar Service - CHANGELOG

## unreleased

## 0.1.4 - 2026-10-19

### Changed

* docs: [status](./docs/status.md) §プラグイン — [S2J Webinar](https://github.com/stein2nd/s2j-webinar) が `GET …/tracking-sources` で `build_tracking_sources_request` / `map_tracking_sources_response` を接続 (運用素材 P2b)。CoverArt / 定義 REST も合わせ Local 完結 `[x]`。本 repo §プラグイン **3/4** (残: ステージング Zoom 外部結合)

## 0.1.3 - 2026-10-19

### Changed

* docs: [status](./docs/status.md) §プラグイン — [S2J Webinar](https://github.com/stein2nd/s2j-webinar) 側の段階1〜2手動 Pass (2026-10-17)、品質スモーク未了1項 (Service §プラグイン相互 `[x]`)、プラグイン [operational_material_index](https://github.com/stein2nd/s2j-webinar/blob/main/docs/operational_material_index.md) §実装優先順を注記
* docs: [status](./docs/status.md) §プラグイン — 運用素材 P2a (`tracking-source-definitions` REST、Source QR 引き渡し) と Gutenberg panelist / GatherPress join URL 修正を [S2J Webinar](https://github.com/stein2nd/s2j-webinar) 進捗として注記 (tracking 取り込み REST → Service 写像は引き続き `[ ]`)

## 0.1.2 - 2026-10-16

### Fixed

* CI: `scripts/verify/all.sh` (bash) — Actions ランナーに zsh がなく php-quality が失敗していた問題

## 0.1.1 - 2026-10-16

### Added

* Composer: `lint:php` (PHPStan + PHPCS)、`phpcbf`
* npm: `lint:php`、`test:unit:php:local` (preinstall `composer install`)

## 0.1.0 - 2026-10-15

### Changed

* dev: `@s2j/docs-linter` ^1.0.29 (lockfile 更新)
* docs lint: `.textlintrc.json` — [status](./docs/status.md) 見出しアンカー `#status-*` を prh 除外

## 0.1.0 - 2026-10-14

### Changed

* docs: [status](./docs/status.md) §プラグイン — [S2J Webinar](https://github.com/stein2nd/s2j-webinar) 結合進捗 (require / SyncRunner `[x]`、tracking / ステージング `[ ]`)、品質スモーク残 (段階3b / 段階1–2) を注記

## 0.1.0 - 2026-10-13

Packagist 初回配布 (`s2j/webinar-service`)。

### Added

* Packagist [s2j/webinar-service](https://packagist.org/packages/s2j/webinar-service) 登録 (GitHub auto-update / dev-main)

* PHP ライブラリ初版 (`src/`、`S2J\WebinarService\` 公開関数、Zoom 記述子): 正規化・検証、操作計画、Panelist 差分、CRUD/panelist/survey 材料、結果写像、OAuth 材料、tracking 取り込み
* PHPUnit: 102件 (request/result/survey/tracking 仕様行、契約 JSON `tests/Fixtures/contracts/`、モック provider 回帰)
* CI `php-quality` / `release` (タグ): `scripts/verify/all.zsh` (validate / dist / PHPUnit / PHPStan / PHPCS)
* `composer validate-composer` / `composer verify-dist`、Packagist 向け `composer.json` メタ
* README: `composer require` / PHP 8.1+、[build_and_release](./docs/engineering/build_and_release.md)
* npm: `lint:docs`、`quality`、PHP 用 `test` / `phpstan` / `phpcs`

### Changed

* docs: [build_and_release](./docs/engineering/build_and_release.md) の Hook 確認、[status](./docs/status.md) Packagist 完了
* npm: Webinar 向け `scripts` (`quality` → `scripts/verify/all.zsh`)、`private: true`、Similarity 由来の codegen / TS 用 script 削除

* 検証: 登壇者が複数行のとき、不足コードを入力順で返す (`panelist_incomplete` / `panelist_email_invalid`)
* `ProviderRegistry::register` / `resetForTesting` (テスト用)
* `build_survey_update_request`: `webinar_title` 任意時の SoT 委譲を spec どおりに
* docs: [status](./docs/status.md) 完了条件チェックリスト、[ci](./docs/engineering/ci.md) verify / release

## 0.0.1 - 2026-10-12

### Changed

* docs: tracking 取り込みの S2J Webinar 参照を `docs/` 正本 (handoff / glossary / operational index) に更新
* docs_mod: tracking 索引、status の外部リンクを同様に更新

## 0.0.1 - 2026-10-11

### Added

* docs (v0.0.2): Source Tracking 取り込みを `docs_mod/` から確定移行 — [tracking_import_spec](./docs/core/tracking_import_spec.md)、[tracking_import_contract](./docs/contracts/tracking_import_contract.md)、[tracking_import_php_api](./docs/interfaces/tracking_import_php_api.md)
* docs: `zoom` GET 写像、`build_tracking_sources_request` / `map_tracking_sources_response`、tracking 専用 deficiencies (`tracking_http_error` 等)。Webinar 同期 `plan` / `build_webinar_request` には載せない

### Changed

* docs: [specs](./docs/specs.md) / [provider_spec](./docs/core/provider_spec.md) (Zoom 表) / [php_api_spec](./docs/interfaces/php_api_spec.md) / [status](./docs/status.md) に tracking 索引を追記
* docs: [docs_mod/README](./docs_mod/README.md) と [docs_mod/status](./docs_mod/status.md) を正本パス・進捗表記に更新 (起草表記を除去)

## 0.0.1 - 2026-10-10

### Changed

* `intend_retry` (id 非空) は手順3 (`dirty`) と同じ計画と明記。testing に期待列を追記
* OAuth token/refresh の RequestMaterial に method / フル URL の `path` / body キーを固定。Basic はプラグイン側
* survey_map を `answer_kind` 前提の表に直し、プラグイン SPECS リンクを `docs/specs.md` に更新
* `build_survey_update_request` の `webinar_title` を任意 (`null` 可)。architecture ツリーの ASCII を修正

## 0.0.1 - 2026-10-09

### Added

* Composer 実装向けに、Similarity / Survey Service の `docs/` 構成に倣った仕様分割を `docs_mod/` に追加 (`specs.md` 起点、overview / concept / architecture / principles、core、contracts、interfaces、engineering、governance、testing、status、archive)
* プロバイダ・レジストリ仕様を追加 (`docs_mod/core/provider_spec.md`)。Slug Generater の記述子 + Adapter 関数形に倣い、OpenAPI codegen は持たない

### Changed

* `docs_mod/` レジストリ BP: 公開関数は記述子への薄いファサード、`build_webinar_request` は `{ material?, deficiencies }`、未知 provider は不足、OAuth / request / result は Zoom 記述子の正本、用語 (記述子 / Adapter / HTTP Adapter) を辞書で固定、status にレジストリ行、architecture ツリー体裁
* `docs_mod/service_spec.md` を統合見取り図に整理。規則の正本は `core/`。参加登録デフォルトは `0` で統一。Survey Service リンクを `docs/specs.md` に更新
* `docs_mod/` の監査 BP を反映: survey 添付を `attach_survey` に一本化、計画 context / `operations[]` 形の固定、`plan` 内 diff と dirty 所有の断言、未知 provider は不足、get は status 非変更、`duration_minutes` は1以上、`start_at` ワイヤ例、status 正規化 (id 空→`not_created`、欠落+id →`dirty`)、panelist/survey 成功写像
* `docs_mod/` 再監査 BP: `attach_survey` 要素に `survey_document` 必須、OAuth に `webinar:update:survey`、concept を単一 plan ループに、結果写像は2xx=成功、`none` は空配列、dirty は本体項目のみ、get/delete 失敗写像、architecture ツリー体裁
* `docs_mod/` 再々監査 BP: id 空の作成失敗は `error` 維持、usage は失敗で列中断、survey PATCH の `custom_survey` 包み、operations 列順、`intend_retry` は id 非空向け、OAuth は表スコープを一括要求
* `docs_mod/` 軽微 BP: 手順3見出しに id 空 error、usage break 注記、結果写像に204/null、testing 列順、`internal_name` は初版送らない
* `docs_mod/` 境界 BP: メタキー名はプラグイン仕様に閉じる、Panelist 追加ボディ形を固定、メール正規化は trim + ASCII 小文字
* `docs_mod/`: メールの trim + ASCII 小文字は差分・リクエスト組立のみ。`normalize_webinar_record` はレコードに書き戻さない
* プロバイダはアダプタで差し替え可能とする。初版は `zoom` のみ。空実装は置かない。管理画面での選択はプラグイン側
* README を `docs_mod/` の索引・公開面への導線に更新
* `npm run lint:docs` の対象に `docs_mod/**/*.md` を追加
* `docs_mod/` Adapter BP: Adapter 狭い戻りと公開封筒、`map_result` に `start_url`、`map` 未知 provider は deficiencies、operation は意味のみ、Zoom 実装は `Providers/`、survey は `build_request` 分岐、OAuth kind 別戻り、用語 Deficiency、Panelist 削除 ID は Zoom 注記、`adapters/http` 非配置
* `docs_mod/` 追随 BP: 原則の例外範囲、service_spec の `start_url` / create パス、operation remove は意味のみ、survey_map 見出し、`provider_unsupported` は map も含む
* `docs_mod/contracts/data_dictionary.md`: `provider_unsupported` を検証・リクエスト組立・応答写像の三面にそろえる
* `docs_mod/`: build の材料不能 (id 空等) は例外。不足コードは validation 一覧のみ。正規化デフォルトを record_spec / data_contract でそろえる
* `docs_mod/contracts/data_dictionary.md`: Deficiency は公開封筒・検証のコード。Adapter `build_request` の戻りではない、と明記
* `docs_mod/contracts/data_contract_spec.md`: `map_webinar_response` の戻りを `record` / `start_url?` / `deficiencies` に固定。プログラマー誤りは `InvalidArgumentException`
* `docs_mod/core/oauth_spec.md`: `oauth_materials` の kind 別戻り (`authorize` は URL 文字列、`token` / `refresh` はリクエスト材料) を表で固定
* `docs_mod/core/provider_spec.md`: survey は独立 Adapter フィールドにせず `build_request` の `attach_survey` 分岐。必須 step / id / OAuth config 欠落は例外
* 合意済み仕様キットを `docs_mod/` から `docs/` に移行。`docs_mod/` は改訂案・進行中イニシアチブの起草用シェルとして残す。README を `docs/` 導線に更新
* `docs/` BP: `plan` は再 validate しない (不足ゲートは呼び出し側)、`intend_get` は書き込み列末尾 (`delete` 単独は付けない)、`map_result` 戻り `{ record, start_url? }`、map 未知 op は例外、build/map/OAuth のみレジストリ・ファサード、survey ヘルパは非 SoT、Panelist 全員一括 DELETE 不使用

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
