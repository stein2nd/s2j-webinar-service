<!--
目的：「プロバイダ・レジストリ (記述子 + 写像)」の明文化
参考: S2J Slug Generater docs/05-providers.md (薄いレジストリ。OpenAPI codegen は持たない)
-->

# S2J Webinar Service - プロバイダ・レジストリ

← [索引](../specs.md)

[S2J Slug Generater の翻訳プロバイダ](https://github.com/stein2nd/s2j-slug-generater/blob/main/docs/05-providers.md) と同様に、Abstract Factory のクラス階層は置きません。**データ (記述子) + Adapter 関数** のレジストリにします。クラスを増やさず、記述子を1件追加して拡張します。

* Clean Coding: プロバイダ固有のパス・ボディ・応答の解釈は、その記述子の写像に閉じる。パイプラインからは共通の操作とリクエスト材料だけが見える。
* Similarity Service の OpenAPI / codegen / Embedding Strategy 群は持たない。HTTP 実行はプラグインである。

## レジストリ

```text
providers: Map<ProviderId, WebinarProvider>

lookupProvider(id: ProviderId): Result<WebinarProvider, provider_unsupported>
```

* `ProviderId` は、レコードの `provider` (文字列)。欠落時の正規化は、`zoom` ([record_spec.md](./record_spec.md))。
* 未知 ID は例外にせず、不足 `provider_unsupported` ([validation_spec.md](./validation_spec.md))。

## 記述子のフィールド

操作計画、Panelist 差分、レコード検証の骨格は、プロバイダ共通です。差分がある写像だけを記述子に置きます。**survey は、独立 Adapter フィールドを置きません。** `build_request` の `attach_survey` 分岐でボディを組み、正本はそのプロバイダの [survey_map_spec.md](./survey_map_spec.md) (初版 Zoom)。

| フィールド | 層 | 用途 |
| --- | --- | --- |
| `id` | データ | レコードの `provider` |
| `label` | データ | 表示名のヒント (i18n はプラグイン境界) |
| `docs_url` | データ | 接続、OAuth の案内 URL (任意) |
| `build_request` | Adapter | 操作要素 → リクエスト材料 ([request_spec.md](./request_spec.md)) |
| `map_result` | Adapter | HTTP ステータスとボディ → レコード更新 ([result_spec.md](./result_spec.md)) |
| `oauth_materials` | Adapter | 認可・トークン交換の材料 ([oauth_spec.md](./oauth_spec.md))。不要なら空 |

## 組込みプロバイダ

### Zoom (`zoom`)

初版の唯一の実装です。

| 項目 | 正本 |
| --- | --- |
| 作成・更新・削除・取得、Panelist | [request_spec.md](./request_spec.md) |
| 応答写像 | [result_spec.md](./result_spec.md) |
| OAuth スコープ | [oauth_spec.md](./oauth_spec.md) |
| アンケート PATCH | [survey_map_spec.md](./survey_map_spec.md) |

使わない接続先 (Teams / Meet 等) の空実装は、置きません。

## Adapter 契約

実装は、クラス必須ではありません。関数モジュールでよいです。

```text
build_request(op, record, step) -> RequestMaterial
map_result(op, status, body, record) -> { record, start_url? }
oauth_materials('authorize', config) -> string
oauth_materials('token'|'refresh', config, ...) -> RequestMaterial
```

* 不足コード (Deficiency) の用語は [../contracts/data_dictionary.md](../contracts/data_dictionary.md)。初版の公開 `build` / `map` で使う不足は一覧のコードのみ (`provider_unsupported` 等)。材料不能用の別コードは置かない。`build_request` は Deficiency を返さない。
* 公開の `build_webinar_request` はファサードの封筒 `{ material?, deficiencies: string[] }` を返す ([../interfaces/php_api_spec.md](../interfaces/php_api_spec.md))。未知 `provider` は lookup 失敗で不足。Adapter には来ない。PHP シグネチャは Adapter と一致させない。
* 公開の `map_webinar_response` は `map_result` の戻りに加え、未知 `provider` 時は `deficiencies: ['provider_unsupported']` を載せる (レコードおよび status は不変)。
* 未知の `op`、計画 op の必須 step 欠落 (`attach_survey` の `survey_document` 等)、id 非空前提 op で `webinar_id` 空は `InvalidArgumentException`。
* `oauth_materials`: 必須 `config` キー欠落は `InvalidArgumentException` (設定ミス。レコード不足チャネルと混ぜない)。
* `Authorization` は材料に含めない。トークン付与と HTTP 実行はプラグイン。
* プロバイダ固有のエラー文は `last_error` に載せてよい。トークンとシークレットは載せない。
* Zoom のパス / body 正本は [request_spec.md](./request_spec.md)。OAuth スコープ正本は [oauth_spec.md](./oauth_spec.md)。記述子はそれらに委譲し、二重実装しない。

## 追加手順 (コントリビュータ)

1. 記述子を1件足す (`id`、案内、`build_request` / `map_result`、必要なら `oauth_materials`。survey は `build_request` の `attach_survey` と写像仕様)。
2. レジストリに登録する。
3. その `id` 向けの PHPUnit (未知 ID は `provider_unsupported`、組込みは既存分岐を壊さない)。
4. 仕様に組込み表を1行足す。プラグイン側は、実装済みが増えたら選択 UI を出す ([S2J Webinar specs](https://github.com/stein2nd/s2j-webinar/blob/main/docs_mod/specs.md))。

## 設定との関係

| 置き場 | 持つもの |
| --- | --- |
| 本ライブラリ | レジストリ、記述子、写像 |
| プラグイン | 選択 UI (実装済みが増えた場合)、OAuth クライアント、トークン、HTTP、メタの `provider` |

## 関連

* 原則: [principles.md](../principles.md) §6
* レコードの `provider`: [record_spec.md](./record_spec.md)
* 用語: [data_dictionary.md](../contracts/data_dictionary.md)
