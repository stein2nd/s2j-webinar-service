<!--
目的：「Survey 文書 → Zoom survey PATCH」の明文化
-->

# S2J Webinar Service - アンケート写像仕様

本ファイルは **Zoom (`zoom`) 記述子** の `build_request( 'attach_survey' )` 写像正本です。組立入口は `build_webinar_request( 'attach_survey', … )`。

## 責務

* Survey Service 形の設問文書を、`PATCH /webinars/{webinarId}/survey` (`webinarSurveyUpdate`) のボディに写すこと。

## 非責務

* 設問の検査・助言 (Survey Service)
* 投票 (poll) と登録フォームの質問
* 回答レポート (`GET /report/webinars/{webinarId}/survey`)

## 前提

* 入力文書は、プラグインが `ready` と判定したものだけを渡す。本ライブラリは文書の `status` を再判定しない。
* 設問規則の正本は [Survey Service docs](https://github.com/stein2nd/s2j-webinar-survey-service/blob/main/docs/specs.md) である。
* 公開の組立入口は `build_webinar_request( 'attach_survey', … )` である。survey 未完了を create の条件にしない (後から同じ操作で付ける)。

## ボディのトップ形 (初版)

```json
{
  "show_in_the_browser": true,
  "show_in_the_follow_up_email": false,
  "custom_survey": {
    "title": "ウェビナータイトル",
    "questions": []
  }
}
```

* `custom_survey.title` は操作要素の `webinar_title`、欠落時はレコードの `topic`。説明文は空 (キーを送らない)。
* `third_party_survey` は初版では送らない。
* `questions` の各要素は下記のキー写像に従う。

## 初版で送る type

| Zoom UI | API `type` | 初版 |
| --- | --- | --- |
| 単一選択 | `single` | 送る |
| 複数選択 | `multiple` | 送る |
| 短い回答 | `short_answer` | 送る |
| 長い回答 | `long_answer` | 送る |
| レーティング・スケール | `rating_scale` | 送る |
| マッチング / ランク順 / 空欄記入 | `matching` / `rank_order` / `fill_in_the_blank` | 送らない |

## キー写像

設問は **`answer_kind`** で分岐する。文書トップや設問に `single` / `short` などのキーはない (値は `answer_kind` に入る)。

文書にあっても初版では送らないもの: `internal_name`、調査のメタ情報、`show_as_dropdown`、重み、画像、スキップロジック。

| 文書 (フィールド / 条件) | Zoom |
| --- | --- |
| `prompt` | `name` |
| `required` | `answer_required` |
| `answer_kind` = `single` かつ `choices` | `type` = `single`、`answers` = `choices` |
| `answer_kind` = `multiple` かつ `choices` | `type` = `multiple`、`answers` = `choices` |
| `answer_kind` = `short` | `type` = `short_answer`。文字数キーは送らない (API デフォルト) |
| `answer_kind` = `long` | `type` = `long_answer`。同上 |
| `answer_kind` = `rating` の `score_min` | `rating_min_value` |
| `answer_kind` = `rating` の `score_max` | `rating_max_value` |
| `answer_kind` = `rating` の `label_low` | `rating_min_label` |
| `answer_kind` = `rating` の `label_high` | `rating_max_label` |

## 関連

* リクエスト: [request_spec.md](./request_spec.md)
* Survey Service: [document_spec](https://github.com/stein2nd/s2j-webinar-survey-service/blob/main/docs/core/document_spec.md)
