<!--
目的：「Packagist とセマンティックバージョン」の明文化
-->

# S2J Webinar Service - ビルドおよびリリース

本ドキュメントは、Composer パッケージとしての **配布とバージョン** を定義します。

## 設計意図 (ゴール)

プラグインが Packagist の名前だけで require できる状態を維持します ([usage_spec.md](../interfaces/usage_spec.md))。

## 配布

* Packagist 名: `s2j/webinar-service`
* GitHub: `stein2nd/s2j-webinar-service`
* タグは SemVer (`vMAJOR.MINOR.PATCH`)。公開 API / 不足コードの互換は [php_api_spec.md](../interfaces/php_api_spec.md)。

## CHANGELOG

* ユーザーまたは呼び出し側に見える変更を unreleased に書く。
* リリース時に unreleased をバージョン節へ移し、日付を付ける。
* `docs_mod/` のみの起草は、`docs/` に反映するまで CHANGELOG に載せなくてよい。

## export-ignore

* `.gitattributes` の `export-ignore` で、テスト・ CI ・エディター設定、および `docs` / `docs_mod` を dist から除外する。
* ローカル検証: `composer verify-dist` ([scripts/verify-composer-dist.sh](../../scripts/verify-composer-dist.sh))。

## Packagist 初回登録 (1回)

Packagist 側の作業です (本 repo の CI では代替できません)。

1. [packagist.org](https://packagist.org/) にログインし、**Submit** から Repository URL を登録する。  
   `https://github.com/stein2nd/s2j-webinar-service`
2. パッケージ名が **`s2j/webinar-service`** と一致することを確認する (`composer.json` の `name` と同じ)。
3. **GitHub Service Hook** (Auto-update) を有効にする。以降、SemVer タグの push で Packagist が更新される。
4. (任意) API 更新を CI からも行う場合は、repo の Secrets に `PACKAGIST_USERNAME` と `PACKAGIST_TOKEN` を設定する ([release.yml](../../.github/workflows/release.yml))。

## リリース手順

1. `main` が緑 (`php-quality` / 必要なら `docs-lint`) であることを確認する。
2. [CHANGELOG.md](../../CHANGELOG.md) の **unreleased** を、次のバージョン節 (例: `0.1.0`) に移す。
3. コミットを push する。
4. SemVer タグを付けて push する (例: `v0.1.0`)。

   ```bash
   git tag -a v0.1.0 -m "v0.1.0"
   git push origin v0.1.0
   ```

5. GitHub Actions の **Release** ワークフローがタグで走り、品質ゲートを通す。
6. [Packagist のパッケージページ](https://packagist.org/packages/s2j/webinar-service) でタグが取り込まれたことを確認する。
7. 別 repo (S2J Webinar プラグイン) で `composer require s2j/webinar-service:^0.1` 等を更新する (別 PR)。

## 関連

* CI: [ci.md](./ci.md)
* CHANGELOG: [../../CHANGELOG.md](../../CHANGELOG.md)
