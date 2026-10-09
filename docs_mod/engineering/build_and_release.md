<!--
目的：「Packagist とセマンティックバージョン」の明文化
-->

# S2J Webinar Service - ビルドおよびリリース

## 配布

* Packagist 名: `s2j/webinar-service`
* タグは SemVer。公開 API / 不足コードの互換は [../interfaces/php_api_spec.md](../interfaces/php_api_spec.md)。

## CHANGELOG

* ユーザーまたは呼び出し側に見える変更を unreleased に書く。
* `docs_mod/` のみの起草は、`docs/` に反映するまで CHANGELOG に載せなくてよい (いまは起草段階のため、キット追加は unreleased に一行あってよい)。

## export-ignore

* `.gitattributes` でテスト・ CI ・エディター設定、および `docs` / `docs_mod` の扱いを明確にする。

## 関連

* CI: [ci.md](./ci.md)
