<!--
目的：「v0.0.2 Source Tracking 取り込み 進捗」
-->

# s2j-webinar-service - Source Tracking 取り込み (実装状況)

本ページは v0.0.2連携 (Webinar / Source QR) の **起草・確定** 進捗です。仕様正本は `docs/` です。

最終更新: 2026-10-11

## 仕様 (`docs/`)

| 項目 | 状態 | パス |
| --- | --- | --- |
| 取り込み写像 (`zoom`) | 仕様確定 | [core/tracking_import_spec.md](../docs/core/tracking_import_spec.md) |
| 入出力 DTO | 仕様確定 | [contracts/tracking_import_contract.md](../docs/contracts/tracking_import_contract.md) |
| PHP 公開面 | 仕様確定 | [interfaces/tracking_import_php_api.md](../docs/interfaces/tracking_import_php_api.md) |
| `src/` 実装 | 未着手 | 上記 spec に従う |

## エコシステム整合

| リポジトリ | 参照 |
| --- | --- |
| S2J Webinar | [source_qr_handoff_spec.md](https://github.com/stein2nd/s2j-webinar/blob/main/docs/source_qr_handoff_spec.md) |
| Source QR | [persistence_spec.md](https://github.com/stein2nd/s2j-webinar-source-qr/blob/main/docs/persistence_spec.md) (マージ規則) |
| Webinar v0.0.2 - status | [status.md](https://github.com/stein2nd/s2j-webinar/blob/main/docs/operational_material_index.md) |

## 完了条件 (実装)

* [specs.md](../docs/specs.md) / [provider_spec.md](../docs/core/provider_spec.md) / [php_api_spec.md](../docs/interfaces/php_api_spec.md) への索引反映済み。
* PHPUnit: [tracking_import_spec.md](../docs/core/tracking_import_spec.md) のテスト観点を満たす。
