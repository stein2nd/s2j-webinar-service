<!--
目的：「実装状況サマリー」の明文化
-->

# S2J Webinar Service - 実装状況

最終更新: 2026-10-09

## 仕様書 (参照元)

* [specs.md](./specs.md) — 索引
* [service_spec.md](./service_spec.md) — 統合見取り図 (要約。規則の正本ではない)
* [core/](./core/record_spec.md) — 規則 (SoT)
* [contracts/data_contract_spec.md](./contracts/data_contract_spec.md) — DTO (SoT)
* [interfaces/php_api_spec.md](./interfaces/php_api_spec.md) — 公開面

## 機能一覧

| 機能名 | 実装済み/未実装 | 実装％ | 完了条件 |
| --- | --- | --- | --- |
| 仕様分割 (`docs/`) | 確定 | — | Survey/Similarity に倣った分割。正本は `docs/`。改訂案は `docs_mod/` |
| Composer スケルトン (`src/` 公開 API) | 未実装 | 0 | [php_api_spec.md](./interfaces/php_api_spec.md) |
| レコード正規化 + 検証 | 未実装 | 0 | [record_spec.md](./core/record_spec.md) / [validation_spec.md](./core/validation_spec.md) |
| プロバイダ・レジストリ | 未実装 | 0 | [provider_spec.md](./core/provider_spec.md)。初版は `zoom` のみ |
| 操作計画 | 未実装 | 0 | [operation_spec.md](./core/operation_spec.md) |
| リクエスト材料 (CRUD + Panelist) | 未実装 | 0 | [request_spec.md](./core/request_spec.md) (Zoom 記述子の正本) |
| Panelist 差分 | 未実装 | 0 | [panelist_spec.md](./core/panelist_spec.md) |
| 結果写像 | 未実装 | 0 | [result_spec.md](./core/result_spec.md) |
| OAuth 材料 | 未実装 | 0 | [oauth_spec.md](./core/oauth_spec.md) |
| アンケート写像 | 未実装 | 0 | [survey_map_spec.md](./core/survey_map_spec.md) |
| PHPUnit / PHPStan / PHPCS | 設定ファイルのみ想定 | 0 | [testing.md](./testing.md) |
| Packagist 公開 | 未実装 | 0 | [build_and_release.md](./engineering/build_and_release.md) |
| プラグインからの require | 未実装 | 0 | S2J Webinar 側 |

## 補足

* HTTP Adapter (プラグインの HTTP 実行) は、初版仕様の公開面に含めない。記述子上の Adapter 関数とは別物 ([data_dictionary.md](./contracts/data_dictionary.md))。
* Meeting、録画取り込み、効果測定は、初版外。
