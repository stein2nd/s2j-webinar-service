<!--
目的：「入出力仕様 (DTO)」の明文化
-->

# S2J Webinar Service - 入出力仕様

## 概要

* 永続化形式はプラグインが決める。本契約は計算の入出力である。
* 規則は Core である。本仕様は形である。
* OpenAPI は初版では使わない。

## WebinarRecord (入力 / 正規化後)

意味は [../core/record_spec.md](../core/record_spec.md) と [data_dictionary.md](./data_dictionary.md)。

**「正規化後あり」** は、キーが存在すること。欠落時の埋め値は、[../core/record_spec.md](../core/record_spec.md) を正本とします。空のまま検証不足になる項目があります。

| フィールド | 型 | 入力 | 正規化後 |
| --- | --- | --- | --- |
| provider | string | 任意 | あり (欠落は `zoom`) |
| topic | string | 任意 (空は不足) | あり (欠落は `""`) |
| agenda | string | 任意 | あり (欠落は `""`) |
| start_at | string | 任意 | あり (欠落は `""`。有効時はタイムゾーン付き瞬間。例: `2026-10-09T15:00:00+09:00`) |
| timezone | string | 任意 | あり (欠落は `""`。有効時は IANA。例: `Asia/Tokyo`) |
| duration_minutes | int | 任意 | あり (欠落は `0`。検証は1以上) |
| auto_recording | enum | 任意 | あり (欠落は `cloud`) |
| approval_type | int | 任意 | あり (欠落は `0`) |
| panelists | `{name,email}[]` | 任意 | あり (欠落は `[]`) |
| webinar_id | string | 任意 | あり (欠落は `""`) |
| webinar_uuid | string | 任意 | あり (欠落は `""`) |
| join_url | string | 任意 | あり (欠落は `""`) |
| status | enum | 任意 | あり (id 空: `error` 維持または `not_created`。id 非空で欠落→`dirty`。正本は record_spec) |
| last_error | string | 任意 | あり (欠落は `""`) |

## 検証結果

| フィールド | 型 | 説明 |
| --- | --- | --- |
| record | WebinarRecord | 正規化後 |
| deficiencies | string[] | 不足コード。空なら書き込み計画可 |

## 操作計画

| フィールド | 型 | 説明 |
| --- | --- | --- |
| operations | object[] | 順に実行。各要素は `op` 必須。形は [../core/operation_spec.md](../core/operation_spec.md) |

下記は、操作要素の例です。

```json
[
  { "op": "create" },
  { "op": "add_panelists", "panelists": [ { "name": "山田太郎", "email": "taro@example.co.jp" } ] },
  { "op": "remove_panelists", "email": "old@example.co.jp" },
  {
    "op": "attach_survey",
    "survey_document": { "questions": [] },
    "webinar_title": "例タイトル"
  }
]
```

何もしない場合は `[]` です。`none` は、返しません。

## 計画コンテキスト (入力)

| キー | 型 | 説明 |
| --- | --- | --- |
| previous_panelists | `{name,email}[]` | 任意 |
| intend_delete | bool | 任意 |
| intend_get | bool | 任意 |
| intend_retry | bool | 任意 (`error` 再試行) |
| survey_document | object\|null | 任意。非空=添付意図 |
| webinar_title | string | 任意 |

## RequestMaterial

| フィールド | 型 | 説明 |
| --- | --- | --- |
| method | string | `GET` / `POST` / `PATCH` / `DELETE` |
| `path` | string | Webinar REST: ホストなし (例: `/users/me/webinars`)。OAuth token/refresh: フル URL (例: `https://zoom.us/oauth/token`)。正本は [../core/request_spec.md](../core/request_spec.md) / [../core/oauth_spec.md](../core/oauth_spec.md) |
| body | object\|null | Authorization / Basic なし (プラグインが付ける) |

### build_webinar_request の戻り

| フィールド | 型 | 説明 |
| --- | --- | --- |
| material | RequestMaterial | 成功時のみ |
| deficiencies | string[] | 空なら成功。未知 `provider` 等はここにコード |

create ボディの時刻例: `start_time` = `2026-10-09T15:00:00`、`timezone` = `Asia/Tokyo` (レコードの `start_at` / `timezone` から。正本は [../core/request_spec.md](../core/request_spec.md)。Zoom 記述子)。

### add_panelists ボディ (初版固定)

正本は [../core/request_spec.md](../core/request_spec.md)。形:

```json
{
  "panelists": [
    { "name": "山田太郎", "email": "taro@example.co.jp" }
  ]
}
```

`email` は [../core/panelist_spec.md](../core/panelist_spec.md) の正規化後。

## Panelist 差分

| フィールド | 型 |
| --- | --- |
| add | `{ name, email }[]` (正規化後のメール) |
| remove | `{ email }[]` (正規化後のメール) |

## エラー

* プログラマー誤り (計画に存在しない `op` 文字列、計画 op の必須 step 欠落、id 非空前提 op で `webinar_id` 空、OAuth 必須 config 欠落等) は `InvalidArgumentException` でよい。
* レコード項目の不足と未知 `provider` は例外にせず `deficiencies` で返す (`validate`、`build_webinar_request`、`map_webinar_response`)。材料不能用の別不足コードは初版では置かない。

### map_webinar_response の戻り

| フィールド | 型 | 説明 |
| --- | --- | --- |
| record | WebinarRecord | 写像後 (未知 provider 時は入力と同じ) |
| start_url | string | 任意。揮発 |
| deficiencies | string[] | 空なら成功。未知 provider は `provider_unsupported` |

## 関連

* 辞書: [data_dictionary.md](./data_dictionary.md)
* PHP API: [../interfaces/php_api_spec.md](../interfaces/php_api_spec.md)
