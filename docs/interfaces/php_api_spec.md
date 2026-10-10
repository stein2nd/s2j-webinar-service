<!--
目的：「Composer から見える PHP 公開面」の明文化
-->

# S2J Webinar Service - PHP 公開面

## パッケージ

名前は実装時に PSR-4の配置に落とし込みます。意味は変えません。

| 項目 | 値 |
| --- | --- |
| Composer 名 | `s2j/webinar-service` |
| 名前空間 | `S2J\WebinarService\` |
| PHP | `>=8.1` |

## 公開 API (初版)

うち **build / map / OAuth** は [../core/provider_spec.md](../core/provider_spec.md) のレジストリを引く **薄いファサード** です。プロバイダ固有のパス / body / 応答解釈 / OAuth 材料は、記述子の Adapter に閉じます。レジストリ・ファサードと共通 Core に Zoom 専用分岐を置きません。任意ヘルパは非 SoT ・ Zoom 専用として明示した場合のみ可です。`normalize` / `validate` / `plan` / `diff_panelists` は共通 Core であり、レジストリ非経由です。

| 種別 | 公開関数 | 委譲先 (記述子) |
| --- | --- | --- |
| レジストリ・ファサード | `build_webinar_request` | `lookupProvider(record.provider)` → `build_request` |
| レジストリ・ファサード | `map_webinar_response` | 同上 → `map_result` |
| レジストリ・ファサード | `build_oauth_*` | 初版は `lookupProvider( 'zoom' )` → `oauth_materials` (レコードなし。他プロバイダ追加時に拡張) |
| 共通 Core | `normalize_webinar_record` / `validate_webinar_record` / `plan_webinar_operations` / `diff_panelists` | (なし) |

### normalize_webinar_record / validate_webinar_record

* `validate` は、内部で正規化してから検証してよい。
* 不足は `deficiencies`。規則は [../core/validation_spec.md](../core/validation_spec.md)。
* 未知の `provider` (レジストリに記述子がない値) は不足 `provider_unsupported`。例外にしない。初版は `zoom` のみ。

```php
/** @param array<string, mixed> $record
 *  @return array<string, mixed> 正規化後レコード
 */
function normalize_webinar_record(array $record): array;

/**
 * @param array<string, mixed> $record
 * @return array{record: array<string, mixed>, deficiencies: list<string>}
 */
function validate_webinar_record(array $record): array;
```

### plan_webinar_operations

規則とコンテキストキー、`operations[]` の形は [../core/operation_spec.md](../core/operation_spec.md)。

**本関数は `validate` を呼びません。** 呼び出し側が不足ゼロのレコードを渡すのが通常です。

Panelist 差分は本関数が `previous_panelists` から内部で計算します ([../core/panelist_spec.md](../core/panelist_spec.md) と同じ規則。二重実装しない)。

```php
/**
 * @param array<string, mixed> $record
 * @param array<string, mixed> $context previous_panelists?, intend_delete?, intend_get?, intend_retry?, survey_document?, webinar_title?
 * @return list<array<string, mixed>> operations  各要素は op 必須。add_panelists は panelists、remove_panelists は email、attach_survey は survey_document 必須。空なら何もしない (none は返さない)
 */
function plan_webinar_operations(array $record, array $context = []): array;
```

### build_webinar_request

Survey 添付もこの入口です (`attach_survey`)。

**通常は `plan_webinar_operations` が返した操作要素 `$step` を、そのまま第3引数に渡します。** `attach_survey` では要素内の `survey_document` が必須です ([../core/operation_spec.md](../core/operation_spec.md))。plan 経由で欠落した `survey_document` は **プログラマー誤り** で、`InvalidArgumentException` です (不足コードにはしない)。

Adapter の `build_request` は `RequestMaterial` を返します。公開面はファサードの封筒 `{ material?, deficiencies: string[] }` です ([../core/provider_spec.md](../core/provider_spec.md))。

| ケース | 戻り |
| --- | --- |
| 成功 | `deficiencies` は空。`material` に method / パス / body |
| 未知 `provider` | `deficiencies: ['provider_unsupported']`。`material` なし。**例外にしない** |
| 未知の `$op`、必須 step 欠落、id 非空前提 op で `webinar_id` 空 | `InvalidArgumentException` (プログラマー誤り。不足コードは増やさない) |

`validate` 済み・`plan` 経由が通常です。validate を飛ばしても、未知 provider は不足で返します。

```php
/**
 * @param string $op create|update|delete|get|add_panelists|remove_panelists|attach_survey
 * @param array<string, mixed> $record
 * @param array<string, mixed> $step plan が返した操作要素 (op に応じて panelists / email / survey_document 等)
 * @return array{
 *   material?: array{method: string, path: string, body: array<string, mixed>|null},
 *   deficiencies: list<string>
 * }
 */
function build_webinar_request(string $op, array $record, array $step = []): array;
```

### diff_panelists

[panelist_spec.md](../core/panelist_spec.md) と同じ規則の **公開ヘルパー**です。`plan_webinar_operations` が内部で使う実装と共有します。プラグインが単体で差分だけ見たい場合に使います。

```php
/**
 * @param list<array{name?: string, email?: string}> $previous
 * @param list<array{name?: string, email?: string}> $current
 * @return array{add: list<array{name: string, email: string}>, remove: list<array{email: string}>}
 */
function diff_panelists(array $previous, array $current): array;
```

### map_webinar_response

`lookupProvider(record.provider)` → `map_result` に委譲します。`map_result` は `{ record, start_url? }` を返し、公開面はそれに `deficiencies` を足します。

| ケース | 戻り |
| --- | --- |
| 成功 | `deficiencies` は空。`record` / 任意 `start_url` |
| 未知 `provider` | `deficiencies: ['provider_unsupported']`。`record` 不変、`status` 不変、`start_url` なし |
| 未知の `$op` | `InvalidArgumentException` (プログラマー誤り。build と同じ) |

`start_url` は揮発です。レコード正本には書きません。規則は [../core/result_spec.md](../core/result_spec.md) (Zoom 記述子の正本)。

```php
/**
 * @param string $op
 * @param int $http_status
 * @param array<string, mixed>|null $body
 * @param array<string, mixed> $record 写像前
 * @return array{
 *   record: array<string, mixed>,
 *   start_url?: string,
 *   deficiencies: list<string>
 * }
 */
function map_webinar_response(string $op, int $http_status, ?array $body, array $record): array;
```

### build_survey_update_request (任意ヘルパ・非 SoT ・ Zoom 専用)

公開の **正本入口 (SoT)** は `build_webinar_request( 'attach_survey', … )` です。本関数はレジストリ汎用ではなく、規則の正本でもありません。利便のために置く場合は、初版 **Zoom 固定** (`lookupProvider( 'zoom' )` → `build_request( 'attach_survey', … )` 相当) で [../core/survey_map_spec.md](../core/survey_map_spec.md) に委譲するだけにし、規則を分岐させません。戻り形は `build_webinar_request` と同じ (`material` / `deficiencies`)。置かない選択も可です。

`$webinar_title` は **任意** (`null` 可。SoT の `attach_survey` と同じ)。欠落または空の場合は操作要素に `webinar_title` を載せない想定で委譲し、記述子がレコードの `topic` にフォールバックする ([../core/operation_spec.md](../core/operation_spec.md) / [../core/survey_map_spec.md](../core/survey_map_spec.md))。本ヘルパがレコードを組み立てる場合も、そのフォールバック規則を分岐させない。

```php
/**
 * @param array<string, mixed> $survey_document ready 文書
 * @param string $webinar_id
 * @param string|null $webinar_title 見出し用 (任意。null / 空は SoT どおり topic フォールバック)
 * @return array{
 *   material?: array{method: string, path: string, body: array<string, mixed>},
 *   deficiencies: list<string>
 * }
 */
function build_survey_update_request(array $survey_document, string $webinar_id, ?string $webinar_title = null): array;
```

### OAuth 材料

公開の `build_oauth_*` は記述子の `oauth_materials` へのファサードです。スコープ表と材料形の正本は [../core/oauth_spec.md](../core/oauth_spec.md) (Zoom 記述子)。規則を公開面に再掲・分岐しません。

`$config` は `client_id` / `client_secret` / `redirect_uri` 等。初版は `zoom` 固定で lookup します。記述子の `oauth_materials( 'authorize', … )` → 認可 URL 文字列、`oauth_materials( 'token'|'refresh', … )` → RequestMaterial。必須キー欠落は `InvalidArgumentException` です。

```php
function build_oauth_authorize_url(array $config): string;
/** @return array{method: string, path: string, body: array<string, mixed>|null} */
function build_oauth_token_exchange_request(array $config, string $code): array;
/** @return array{method: string, path: string, body: array<string, mixed>|null} */
function build_oauth_refresh_request(array $config, string $refresh_token): array;
```

## バージョニング

* 不足コードの **削除・意味変更** は、major。
* コードの **追加** は、minor。
* レコード必須フィールドの **追加** は、major。任意追加は、minor。

## 関連

* プロバイダ・レジストリ: [../core/provider_spec.md](../core/provider_spec.md)
* 契約: [../contracts/data_contract_spec.md](../contracts/data_contract_spec.md)
* 使用方法: [usage_spec.md](./usage_spec.md)
