# S2J Webinar Service - サービス仕様 (ドラフト)

採用・合意前の設計メモとして `docs_mod/` に置き、確定後は `docs/SERVICE_SPEC.md` に移行します。状態はドラフトです。記録日は2026-10-03です。

## 概要

本ドキュメントは、S2J Webinar Service の初期設計における、サービス全体の統合の見取り図を定義します。

呼び出す WordPress プラグインは [S2J Webinar](https://github.com/stein2nd/s2j-webinar) です。プラグイン仕様は [docs_mod/specs.md](https://github.com/stein2nd/s2j-webinar/blob/main/docs_mod/specs.md) です。イベントの画面と投稿は [GatherPress](https://gatherpress.org/) のフォーク [stein2nd/gatherpress](https://github.com/stein2nd/gatherpress) が持ちます。

本ライブラリは **WordPress 非依存** です。GatherPress も知りません。WP フック、設定画面、OAuth トークンの保存、HTTP の実行は扱いません。

KIS2026 の索引で仮称だった `s2j-◯◯◯◯` は、この1本ではありません。後継は次の3つです。

| 層 | 名称 | 状態 |
| --- | --- | --- |
| イベント UI | GatherPress フォーク | [stein2nd/gatherpress](https://github.com/stein2nd/gatherpress)。上流 `develop` に追従する |
| 呼び出し側 | [S2J Webinar](https://github.com/stein2nd/s2j-webinar) | WP プラグイン。仕様ドラフト |
| 計算 | **本ライブラリ** | [s2j-webinar-service](https://github.com/stein2nd/s2j-webinar-service) |

`kis-event-manager` の置き換え先は、この組み合わせです。

## 背景

勤務先では、Zoom Webinar でセミナーを開催しています。案内ページは、都度、manual で書き起こしてます。

検討は、次の問いから始まりました。「イベント管理の画面は、GatherPress に任せ、Webinar の作成と更新を WordPress の管理画面 (GatherPress のコンパニオン・プラグイン) から出来ないものか？」

Zoom Webinar のライセンスを持つログインは、WordPress 管理者とは別です。現行では、そのユーザー (社内では `zoom3@…`) で Zoom に入り、Webinar を作り、登壇者を複数登録します。

作成時には、質問メールの送信先を指定します。送信先は、登壇者リストの1人目 (社内の営業メンバー) の社内メールアドレスです。ライセンスを持つ `zoom3@…` は、営業のファンクションアドレス (たとえば `business@…`) の受信者に入っていません。質問メールは、ホストのアドレスにも、そのファンクションアドレスにも届きません。受け取る本人の社内メールアドレスを、その都度指定します。

案内までの手元の流れは、次のとおりです。

1. Zoom Webinar で ID を発行する。
2. 事前に作った CoverArt を、その回に紐付ける。
3. 流入経路の追跡用に、QR コードを3つ作る。現行は [qr.quel.jp](https://qr.quel.jp/) で、中央にアイコンを入れている。
4. QR の1つを添えたフライヤー PDF を、WordPress のイベントページからダウンロードできるようにする。
5. 配配メールの案内ヘッダーに CoverArt を使い、本文に WordPress のイベントページ URL を書く。

効果測定は Zoom の画面で見ます。クラウド録画のファイルは Zoom に置きます。WordPress のメディアライブラリには持ち込みません。

## 目的

GatherPress のイベントを正として、接続済みの Zoom ユーザーの Webinar を作成・更新・削除・再取得するためのリクエストを組み立てます。

初版の到達点は、次のとおりです。

* WordPress 管理者が、Webinar 権限を持つ Zoom アカウントを OAuth で接続する。作成される Webinar のホストは、その接続ユーザーである。
* イベントのタイトル、開始日時、所要時間、タイムゾーン、録画方式から、Webinar 作成のリクエスト材料を作る。
* 複数の登壇者を Panelist として追加・削除するリクエスト材料を作る。質問メールの送信先は、その1人目の氏名と社内メールアドレスにする。
* 録画方式は `none` / `cloud` / `local` のいずれかである。録画ファイルの転送はしない。
* 作成結果の Webinar ID、UUID、参加 URL を、呼び出し側がイベントに保存できる形で返す。

Meeting (`POST /users/{userId}/meetings`) は作りません。対象は Webinar です。

## 採用しなかったもの

WordPress から Zoom Webinar を作れる既存プラグインは、いくつか存在します。[Events Manager](https://ja.wordpress.org/plugins/events-manager/) と [Events Manager – Zoom Integration](https://ja.wordpress.org/plugins/events-manager-zoom/)、[Video Conferencing with Zoom](https://ja.wordpress.org/plugins/video-conferencing-with-zoom-api/)、[ZooMeet]() です。

これらは採用しません。イベントの画面は GatherPress に固定し、Zoom は配信と録画の基盤に限るためです。既存製品は、それぞれが別のイベント管理を持ちます。CoverArt、追跡用 QR、フライヤーは、どの製品の担当でもありません。

GatherPress 単体も、オンライン会場の URL を書くところまでです。Webinar の作成はしません。

Zoom の管理画面をブラウザ操作で再現することはしません。初回の接続は Zoom の OAuth 画面です。以降は REST API です。

## 非目標 (初版)

* 出席、滞在、投票、Q&A の中身、録画の視聴数を WordPress に取り込むこと。
* クラウド録画のファイルをメディアライブラリに保存すること。
* Zoom の Webinar 作成画面の全項目を WordPress に並べること。
* Zoom 側の手修正を検知して WordPress に戻すこと。
* 申込者を Zoom の registrant として自動登録すること。
* CoverArt、QR、フライヤー PDF、配配メール文面の生成。これらは呼び出し側の後続であり、本ライブラリの外です。
* Microsoft Teams、Google Meet、Cisco Webex。接続先の差し替え口だけ残し、実装は Zoom だけです。
* GatherPress フォーク本体への機能追加。
* WordPress.org への掲載。

## 責務

イベントの親は GatherPress のイベントです。Zoom は、その回の Webinar と録画の正です。

| 機能 | GatherPress | Zoom | 呼び出し側プラグイン | 本ライブラリ |
| --- | --- | --- | --- | --- |
| イベント一覧、イベントページ、イベント URL | ○ | - | - | - |
| Webinar の作成・更新・削除 | - | ○ | 実行 | リクエスト材料 |
| Panelist の追加・削除 | - | ○ | 実行 | 差分とリクエスト材料 |
| 参加 URL、Webinar ID の保存 | - | 発行 | イベントメタ | 結果レコード |
| 録画方式の指定 | - | 実行 | 画面 | `auto_recording` の値 |
| 録画ファイル、効果測定 | - | ○ | 遷移リンクのみ可 | - |
| 質問メールの送信先 | - | 登録の連絡先として受け取る | 1人目の登壇者を渡す | `contact_name` / `contact_email` |
| CoverArt、QR、フライヤー、配配メール | 掲載 | - | 後続 | - |

GatherPress に「Family Tools」という登録制度はありません。公開フック (`gatherpress_` 名前空間) と、コンパニオン・プラグインが使う前提はあります。S2J のコードはフォークに置かず、フックの外側のプラグインに置きます。上流の `develop` に追従できる状態を維持します。

## 現行業務の写像

```text
GatherPress Event
│
├── 基本情報 (タイトル、日時、概要)
│
├── 登壇者 (順序あり)
│    ├── 登壇者 A    ← 1人目。営業メンバー。質問メールの送信先
│    ├── 登壇者 B
│    └── 登壇者 C
│
├── Zoom Webinar
│    ├── Host          接続ユーザー (zoom3@…)
│    ├── 質問メール     登壇者 A の社内メール
│    ├── Panelists
│    └── Recording     none | cloud | local
│
└── (後続。本ライブラリの外)
     ├── CoverArt
     ├── 追跡用 QR × 3
     └── フライヤー PDF
```

質問メールの送信先は、セッション中の Q&A (`settings.question_and_answer`) とは別です。作成時に指定する連絡先であり、API では登録の連絡先 `settings.contact_name` と `settings.contact_email` に載せます。値は登壇者の1人目の氏名と社内メールアドレスです。ホストの `zoom3@…` と、営業のファンクションアドレスは使いません。画面上のラベルは、フィールド突き合わせで作成ウィザードと照合します。

ホストは WordPress のログインユーザーではありません。プラグインの「Zoom と接続」を、Webinar 権限のあるアカウントで許可したとき、以降の作成は `POST /users/me/webinars` です。

## Zoom API との対応

使う操作は、次のとおりです。ユーザー向け OAuth では `{userId}` に `me` を渡します。

| 操作 | メソッド | 初版 |
| --- | --- | --- |
| Webinar 作成 | `POST /users/{userId}/webinars` | ○ |
| 取得 | `GET /webinars/{webinarId}` | ○ |
| 更新 | `PATCH /webinars/{webinarId}` | ○ |
| 削除 | `DELETE /webinars/{webinarId}` | ○ |
| Panelist 追加 | `POST /webinars/{webinarId}/panelists` | ○ |
| Panelist 削除 | 公式の削除メソッド | フィールド突き合わせでパスを確定する |

Webinar の `type` は、単発の `5` だけを初版で使います。繰り返し (`6` / `9`) は使いません。

録画は、`settings.auto_recording` です。値は `local` / `cloud` / `none` です。`cloud` のファイルは Zoom に残します。

参加 URL (`join_url`) は、結果に含めて保存してかまいません。

開始 URL (`start_url`) は、有効期限のあるホスト用 URL です。恒久リンクとして案内に使いません。開く直前に再取得します。

期限の時間数は、実装時に公式で確認します。

指定しない項目は、接続した Zoom ユーザーの既定に任せます。既定が現在の作成 API で継承されるかは、次節の突き合わせで公式を確認します。

公式の入口は、下記です。

* [Webinars](https://developers.zoom.us/docs/api/rest/reference/zoom-api/methods/#tag/Webinars)
* [OAuth](https://developers.zoom.us/docs/integrations/oauth/)

スコープの文字列は、ここには固定しません。

Zoom アプリの種類 (ユーザ管理 OAuth か、アカウント全体か) と、クラシックスコープかグラニュラースコープかが決まってから、作成と Panelist に必要な最小だけを要求します。`webinar:write:admin` 系は、他ユーザーの Webinar を扱うときだけです。初版は接続ユーザー自身の Webinar です。

## フィールドの突き合わせ (実装の前提)

実装の前に、`zoom3` が Zoom の作成画面で実際に入力している項目を一覧し、API のフィールドと本ライブラリの項目に対応させます。会社が使わない項目は、画面に出しません。

初版でライブラリが持つ項目は、次に限る想定です。突き合わせの結果で増減します。

| 項目 | 送り先 |
| --- | --- |
| タイトル | `topic` |
| 概要 | `agenda` (空なら送らない) |
| 開始日時 | `start_time` |
| タイムゾーン | `timezone` |
| 所要時間 (分) | `duration` |
| 種別 | `type` = `5` |
| 録画 | `settings.auto_recording` |
| 質問メールの送信先 (1人目の氏名と社内メール) | `settings.contact_name` / `settings.contact_email` |
| 登壇者の氏名とメール | Panelist 追加の別リクエスト |

登録の要否、承認方法、音声、待機室、チャット、投票、テンプレート、ブランディングは、突き合わせで「毎回指定している」と分かったものだけを足します。

## Composer ライブラリの理由

見た目は WordPress のイベント編集画面ですが、層は次に分かれます。

| 層 | 中身 | 置き場 |
| --- | --- | --- |
| 計算 | Webinar 項目の検証、作成・更新・削除・再取得のリクエスト材料、Panelist の差分、API 応答から結果レコードへの写像 | **本ライブラリ** |
| 副作用 | OAuth、トークン保存、HTTP、GatherPress のメタ、管理画面 | **S2J Webinar** (プラグイン) |
| イベントの正 | タイトル、日時、公開 URL、登壇者の順序 | **GatherPress** |

プラグイン一本に判断を置くと、日時と録画方式の組み合わせをユニットテストするたびに WordPress と Zoom が必要になります。

## 本ライブラリの責務

入力は、Webinar レコードと「今」の時刻です。出力は、次の操作と、操作後のレコードです。HTTP レスポンスの解釈も、渡されたステータスとボディから結果レコードを作るところまでです。

| 責務 | 内容 |
| --- | --- |
| 検証 | タイトルは空でない。開始はタイムゾーン付き。所要時間は正の分数。`auto_recording` は3値のいずれか。登壇者は1人以上で、各自が氏名とメールを持つ。1人目の氏名と社内メールを質問メールの送信先にする |
| 次の操作 | `create` / `update` / `delete` / `get` / `add_panelists` / `remove_panelists` / `none` |
| リクエスト材料 | メソッド、パス、ボディ。`Authorization` は含めない。プラグインがトークンを付ける |
| OAuth の材料 | 認可 URL、認可コード交換、リフレッシュのリクエスト材料。トークンは保存しない |
| Panelist の差分 | 前回のメール一覧と、今回のメール一覧から、追加と削除を分ける |
| 結果の写像 | 成功で Webinar ID、UUID、`join_url`、ホストを埋める。失敗では ID を空のままにし、エラー文を残す。トークンとクライアントシークレットは残さない |

質問メールの送信先は、独立した担当者フィールドにはしません。登壇者リストの1人目から取り、作成と更新のリクエストに含めます。1人目が変わったときも、Webinar の連絡先を更新します。

OAuth のクライアント ID とクライアント・シークレットは、ライブラリに埋め込みません。プラグインがサイト設定から読み、実行時に渡します。リポジトリにコミットしません。

## 状態

レコードが持つ時刻は、タイムゾーン付きの瞬間です。画面の日付と時刻は、WordPress「設定 > 一般」のタイムゾーンで出します。ライブラリは表示文字列を作りません。

```text
provider                 初版は zoom のみ
topic
agenda                   空可
start_at
timezone                 IANA
duration_minutes
auto_recording           none | cloud | local
panelists                順序あり。name と email。1人目が質問メールの送信先
webinar_id               未作成なら空
webinar_uuid             空可
join_url                 空可
status                   not_created | synced | dirty | error
last_error               空、または直近の失敗。トークンは入れない
```

`status` の意味は、次のとおりです。

| status | 意味 |
| --- | --- |
| `not_created` | Webinar ID が空 |
| `synced` | 直近の作成または更新が成功し、そのとき送った項目から WordPress 側が変わっていない |
| `dirty` | 成功のあと、WordPress 側の項目が変わった。まだ Zoom へ送っていない |
| `error` | 直近の API が失敗した。ID は、作成前なら空のまま |

初版は一方向です。WordPress から Zoom に作成・更新します。Zoom で直接直した内容を検知して取り込みません。取り込みは、呼び出し側が再取得を明示したときだけです。再取得は表示用であり、WordPress のタイトルや日時を Zoom の値で上書きしません。

同じ項目での更新を、成功のたびに無条件で繰り返しません。`dirty` のときだけ `update` を返します。

## プラグインの責務 (境界。詳細はプラグイン仕様)

プラグイン仕様の詳細は [S2J Webinar の docs_mod/specs.md](https://github.com/stein2nd/s2j-webinar/blob/main/docs_mod/specs.md) です。ここには境界だけを置きます。

| 責務 | 内容 |
| --- | --- |
| 接続 | 管理画面の「Zoom と接続」。Webinar 権限のあるアカウントで許可し、リフレッシュ・トークンをサイト設定に保存する。WordPress 管理者とは別人でよい |
| 実行 | 本ライブラリが返したリクエストだけを Zoom に送る |
| 関連 | GatherPress のイベントに、プロバイダ、Webinar ID、UUID、`join_url`、`status` を保存する。1イベントにつき Webinar は1つ |
| 登壇者 | 順序付きで保存する。1人目は質問メールを受け取る営業メンバーで、社内メールアドレスを持つ。Zoom には Panelist として渡し、同じ氏名とメールを登録の連絡先にも載せる |
| 画面 | 接続状態、未作成 / 同期済み / 未同期 / 失敗、録画方式、新規登録、更新、再取得、Zoom で開く |
| 効果測定 | 数値は持たない。必要なら Zoom のダッシュボードへのリンクだけ |

GatherPress のフォークには、この画面のコードを入れません。

### 後続 (プラグイン。本ライブラリの外)

イベント公開の成果物は、Zoom 連携のあとでプラグイン側に足します。

* CoverArt は、メディアライブラリの添付であり、イベントの資産です。Zoom のサムネイルにコピーするのは、その後の任意です。
* QR は qr.quel.jp を使わず、WordPress 内で作ります。飛び先は Zoom の ID ではなく、GatherPress のイベント URL です。経路ごとに UTM だけを変えます (flyer / mail / web)。中央のロゴ、色、SVG と PNG はプラグインの仕事です。
* フライヤー PDF と配配メール用のヘッダーは、CoverArt と QR が揃ってから検討します。配配メールへの送信そのものは、このプラグインの外です。
* 公開前チェックリスト (Webinar、CoverArt、QR、フライヤー) は、プラグインの表示です。

## 設計方針

本ライブラリは [kis-wordpress エコシステム仕様](https://github.com/stein2nd/kis-wordpress/blob/main/docs_mod/specs.md) と同じく、FOP + Clean Coding を基本とします。Clean Architecture の定型分割は採用しません。

データの中身は、純粋関数と不変レコードに閉じます。接続先は、プロバイダ名から関数を引く表です。今実装するのは Zoom だけです。使わない接続先の空実装は置きません。

| 借用する原則 | 本ライブラリでの意味 |
| --- | --- |
| 依存の向きは外 → 内 | 純関数は WordPress / GatherPress / HTTP / Zoom SDK を知らない |
| 内側はビジネスルール | Webinar 項目の検証、次の操作、Panelist の差分 |
| 外側は詳細 | プラグインが OAuth、HTTP、GatherPress、画面を持つ |

パッケージ名は `s2j/webinar-service` とします。PHP の名前空間は `S2J\WebinarService\` とします。ライセンスは GPL-3.0-or-later とします。プラグインも同じです。

## 関連リポジトリ

| 名称 | 種別 | 役割 |
| --- | --- | --- |
| **本ライブラリ** | Composer | Webinar の検証、次の操作、リクエスト材料 |
| [S2J Webinar](https://github.com/stein2nd/s2j-webinar) | WP プラグイン | 接続、HTTP、GatherPress のメタ、管理画面 |
| [stein2nd/gatherpress](https://github.com/stein2nd/gatherpress) | WP プラグイン (フォーク) | イベント UI。S2J のコードは置かない |
| [kis-event-manager](https://github.com/yuki-530/kis-event-manager) | WP プラグイン | 現行。段階的に上の組み合わせに差し替える |
| [kis-wordpress](https://github.com/stein2nd/kis-wordpress) | モノレポ | サイト専用プラグイン群。本機能は kis-core に抱え込まない |
| [kis2026_base](https://github.com/stein2nd/kis2026_base) | テーマ | 見た目。イベントの正は GatherPress |

KIS のサイトは、このプラグインのユーザーの一つです。Zoom の OAuth クライアントはサイト設定であり、ライブラリには埋めません。

## 実装順

1. 本ドラフトの合意。
2. `zoom3` の作成画面の入力項目と、Zoom API のフィールドを突き合わせる。スコープと Panelist 削除のパスを、そのとき確定する。
3. 本 repo でスケルトンと純関数の初版 (PHPUnit、WordPress なし、HTTP なし)。
4. プラグインが Composer で require する。OAuth 接続と、GatherPress イベントからの Webinar 作成・結果の保存をつなぐ。
5. 更新、削除、再取得、Panelist の差分、録画方式を足す。
6. CoverArt、QR、フライヤー、公開チェックリストは、プラグインの後続仕様で扱う。

## 本ドラフトの提案

合意前の提案です。

* 製品のコアは、GatherPress のイベントから Zoom Webinar を一方向に作ることである。
* 計算は本ライブラリ、接続と保存は S2J Webinar、イベントの画面は GatherPress フォークである。フォーク本体は改変しない。
* ホストは、OAuth で接続した Zoom ユーザーであり、WordPress 管理者ではない。
* 初版の API は、Webinar の作成、取得、更新、削除と、Panelist の追加・削除である。Meeting は、作らない。
* 録画方式は、`none` / `cloud` / `local`。ファイルは、Zoom に置く。
* 質問メールの送信先は、登壇者の1人目の氏名と社内メールアドレスである。登録の連絡先として Zoom に送る。ホストと営業ファンクションアドレスは使わない。
* 同期は、WordPress から Zoom への一方向である。再取得は、表示用であり、イベントの項目を上書きしない。
* 効果測定、申込者の registrant 登録、CoverArt、QR、フライヤーは、初版の外である。
* OAuth クライアントは、サイト設定であり、配布物に含めない。
* ライセンスは、プラグインとライブラリの両方で GPL-3.0-or-later。
* パッケージ名は `s2j/webinar-service`。プラグインのスラッグは `s2j-webinar`。

## 未決事項

* `zoom3` が毎回入力している作成画面の項目。突き合わせが終わるまで、上の項目表は仮。
* OAuth のアプリ種別と、要求するスコープの文字列。
* Panelist 削除の公式パス。
* `start_url` の有効期限。
* 作成時に省略したフィールドが、接続ユーザーの既定を継承するか。
* セッション中の Q&A (`settings.question_and_answer`) を初版で送るか、会社の既定に任せるか。質問メールの送信先とは別である。
* 参加登録を WordPress から必須にするか。現行の案内はイベントページが入口であり、初版には含めない。
* QR の UTM 名 (`flyer` / `mail` / `web` で足りるか)。プラグインの後続で決める。

## 改訂履歴

| 日付 | 内容 |
| --- | --- |
| 2026-10-03 | 初版ドラフト。GatherPress をイベント UI、本ライブラリを Zoom Webinar のリクエスト組立、呼び出し側プラグインを未作成のコンパニオンとする。一方向同期、録画は Zoom、質問受付はイベントのメタデータ、QR と CoverArt は後続、と記録 |
| 2026-10-03 | 質問受付を、質問メールの送信先に改めた。登壇者1人目の社内メールを `contact_name` / `contact_email` で Zoom に送る。ホストと営業ファンクションアドレスは使わない |
| 2026-10-03 | 呼び出し側プラグイン [s2j-webinar](https://github.com/stein2nd/s2j-webinar) のリポジトリができた。仕様は当該 repo の `docs_mod/specs.md` |
