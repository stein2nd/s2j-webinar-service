# docs_mod (起草用)

確定した仕様の正本は [`../docs/`](../docs/) です。索引は [`../docs/specs.md`](../docs/specs.md) です。

このディレクトリは、**仕様の改訂案** と、**進行中イニシアチブの証跡三点** の起草に使います。

## 仕様ドラフト

1. 改訂案をここに置く (必要なら `docs/` と同じレイヤー構成で)
2. レビューし、合意する
3. 合意内容を `docs/` に反映する
4. 起草ファイルは反映後に片付けてよい (ディレクトリ自体は残す)

## イニシアチブ証跡 (作業中)

進行中の実装・改修では、下記の三点をここに置きます。

* `modification.md`
* `status.md`
* `test-results.md`

完了条件を満たしたら `docs/archive/impl-<slug>/` または `docs/archive/mod-<slug>/` にフリーズし、このディレクトリの三点は削除してかまいません。

詳細は [../docs/governance/documentation_governance.md](../docs/governance/documentation_governance.md) と [../docs/archive/README.md](../docs/archive/README.md) です。

空のままでもかまいません。

## v0.0.2 - Source Tracking 取り込み

2026-10-11に仕様3ファイルを [docs/](../docs/) に確定移行済みです。

| 正本 | 内容 |
| --- | --- |
| [tracking_import_spec.md](../docs/core/tracking_import_spec.md) | `zoom` GET 写像 |
| [tracking_import_contract.md](../docs/contracts/tracking_import_contract.md) | DTO |
| [tracking_import_php_api.md](../docs/interfaces/tracking_import_php_api.md) | 公開面索引 |

進捗: [status.md](./status.md)。関連: [S2J Webinar v0.0.2](https://github.com/stein2nd/s2j-webinar/tree/main/docs_mod/v0.0.2)、[Source QR docs](https://github.com/stein2nd/s2j-webinar-source-qr/tree/main/docs)
