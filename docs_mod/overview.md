<!--
目的：「プロジェクトの存在理由、概要、基本情報」の明文化
-->

# S2J Webinar Service - 概要

本ドキュメントは、本プロジェクトの **基本情報および前提理解** を目的とします。

## はじめに

本プロジェクトは、GatherPress のイベントから Zoom Webinar を作成・更新・削除・再取得するための **リクエスト材料** を組み立てる Composer ライブラリです。WordPress 非依存です。

呼び出す WordPress プラグインは [S2J Webinar](https://github.com/stein2nd/s2j-webinar) です。イベントの画面と投稿は [stein2nd/gatherpress](https://github.com/stein2nd/gatherpress) が持ちます。設問の検査は [S2J Webinar Survey Service](https://github.com/stein2nd/s2j-webinar-survey-service) です。本ライブラリは `ready` の設問文書を Zoom アンケート添付の材料に写せます。

## 基本情報

| 項目 | 値 |
| --- | --- |
| 名称 | S2J Webinar Service |
| Composer 名 | `s2j/webinar-service` |
| 名前空間 | `S2J\WebinarService\` |
| ライセンス | GPL-3.0-or-later |
| PHP | v8.1以上 (想定) |

## 提供機能

* Webinar レコードの検証と正規化
* 次の操作の計画 (`create` / `update` / `delete` / `get` / Panelist 追加・削除 / `attach_survey`。何もしない場合は空配列)
* プロバイダ記述子向けリクエスト材料・応答写像 (**初版は Zoom**)。公開入口はレジストリ経由 (`Authorization` なし)
* Panelist の差分 (メール基準)
* OAuth 認可・トークン交換の材料 (**初版 Zoom**。トークンは保存しない)
* `attach_survey`: Survey 文書 → 接続先アンケート PATCH ボディへの写像 (初版 Zoom)

## 責務

* 上記の計算を、純関数として提供すること。
* 呼び出し側がイベントに保存できる形で、Webinar ID / UUID / `join_url` / `status` を返すこと。

## 非対応スコープ (Out of Scope)

* HTTP の実行、OAuth トークンの保存、WP フック、設定画面
* GatherPress / WordPress / Zoom SDK への依存
* Meeting の作成
* 他会議プロダクトの記述子追加 (初版以降。レジストリの差し替え口は残す)
* 出席・録画ファイル・効果測定の取り込み
* 設問の検査・助言 (Survey Service)
* CoverArt、QR、フライヤー、配配メール
* WordPress.org への掲載

## 関連ドキュメント

* 背景: [concept.md](./concept.md)
* 統合見取り図: [service_spec.md](./service_spec.md)
* 索引: [specs.md](./specs.md)
