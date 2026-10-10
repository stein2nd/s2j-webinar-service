<!--
目的：「OAuth リクエスト材料」の明文化
-->

# S2J Webinar Service - OAuth 材料仕様

本ファイルは **Zoom (`zoom`) 記述子** の `oauth_materials` 正本です。公開の `build_oauth_*` はレジストリ経由の薄いファサードであり、ここに規則を再掲・分岐しません ([../interfaces/php_api_spec.md](../interfaces/php_api_spec.md)、[provider_spec.md](./provider_spec.md))。

## 責務

* 認可 URL、認可コード交換、リフレッシュの **リクエスト材料** を組み立てること。

## 非責務

* リダイレクトの受信、トークンの保存、HTTP の実行 (プラグイン)
* クライアント ID / シークレットの配布物への埋め込み

## アプリ種別

* ユーザー管理の OAuth である。公開マーケットには出さず、サーバー間認証にもしない。
* スコープは本人用のグラニュラーだけである。末尾が `:admin` のものは要求しない。

## スコープ (初版・ Zoom)

| 操作 | スコープ |
| --- | --- |
| 作成 | `webinar:write:webinar` |
| 取得 | `webinar:read:webinar` |
| 更新 | `webinar:update:webinar` |
| 削除 | `webinar:delete:webinar` |
| 登壇者の追加 | `webinar:write:panelist` |
| 登壇者の削除 | `webinar:delete:panelist` |
| アンケート添付 | `webinar:update:survey` |
| 接続ユーザーのメール | `user:read:user` |

## 材料

呼び出し側がサイト設定から `client_id` / `client_secret` / `redirect_uri` を渡します。下記は、記述子 `oauth_materials` の kind ごとの戻りです。

| kind | 戻り |
| --- | --- |
| `authorize` | 認可 URL 文字列 (RequestMaterial ではない) |
| `token` | token エンドポイント向け RequestMaterial |
| `refresh` | リフレッシュ向け RequestMaterial |

必須 `config` キー欠落は `InvalidArgumentException` です。シークレットをログ用フィールドに複製しません。

Webinar REST の RequestMaterial は **API ホストなしの `path`** (`/users/me/webinars` 等。ホストはプラグインが `api.zoom.us` 等を付ける) です。OAuth の token / refresh はホストが違うため、**`path` にフル URL** (`https://zoom.us/oauth/token`) を載せます。プラグインは `path` が `https://` で始まる場合はそのまま使い、そうでなければ API ベースを前置します。

**Basic 認証 (`client_id:client_secret`) は材料に入れない。** Webinar の `Authorization` と同様、プラグインが付ける。

### token / refresh の RequestMaterial (初版)

| kind | method | `path` | body |
| --- | --- | --- | --- |
| `token` | `POST` | `https://zoom.us/oauth/token` | `grant_type` = `authorization_code`、`code` (公開面の引数)、`redirect_uri` (`config`) |
| `refresh` | `POST` | `https://zoom.us/oauth/token` | `grant_type` = `refresh_token`、`refresh_token` (公開面の引数) |

body はキー付き object で返す。プラグインが `application/x-www-form-urlencoded` にエンコードしてよい。

### 認可 URL (初版)

`build_oauth_authorize_url` (≒ `oauth_materials( 'authorize', … )`) は、上記表の本人用スコープを **スペース区切りでまとめて** `scope` に載せます。プラグインがサブセットだけ渡す拡張は後続です。

公式: [OAuth](https://developers.zoom.us/docs/integrations/oauth/)

## 関連

* プロバイダ・レジストリ: [provider_spec.md](./provider_spec.md)
* 公開面: [../interfaces/php_api_spec.md](../interfaces/php_api_spec.md)
