<!--
目的：「Source Tracking 取り込みの PHP 公開面」
-->

# Source Tracking 取り込み — PHP 公開面

規則の正本は [../core/tracking_import_spec.md](../core/tracking_import_spec.md) です。全体の公開 API 表は [php_api_spec.md](./php_api_spec.md) です。

## 公開関数 (レジストリ・ファサード)

| 種別 | 公開関数 | 委譲先 (記述子) |
| --- | --- | --- |
| レジストリ・ファサード | `build_tracking_sources_request` | `lookupProvider` → `build_tracking_sources_request` |
| レジストリ・ファサード | `map_tracking_sources_response` | 同上 → `map_tracking_sources_response` |

## 呼び出し上の注意

* **`plan_webinar_operations` / `build_webinar_request` の `$op` には含めない。**
* 呼び出し側 (S2J Webinar): `webinar_id` は `_s2j_webinar_id`。HTTP 実行後、2xx 以外も含め status を `map` に渡す。
* 戻り `items` を REST `tracking-sources` の `items` にそのまま載せてよい ([handoff 契約](https://github.com/stein2nd/s2j-webinar/blob/main/docs/source_qr_handoff_spec.md))。

シグネチャと戻り表は [tracking_import_spec.md](../core/tracking_import_spec.md#公開-php-api) を正本とする。

## 改訂履歴

| 日付 | 内容 |
| --- | --- |
| 2026-10-11 | v0.0.2公開面を `docs/interfaces/` に確定 |
