# S2J Webinar Service - サービス仕様 (ドラフト)

採用・合意前の設計メモとして `docs_mod/` に置き、確定後は `docs/SERVICE_SPEC.md` に移行します。状態はドラフトです。記録日は2026-10-03です。

## 概要

本ドキュメントは、S2J Webinar Service の初期設計における、サービス全体の統合の見取り図を定義します。

呼び出す WordPress プラグインは [S2J Webinar](https://github.com/stein2nd/s2j-webinar) です。プラグイン仕様は [docs_mod/specs.md](https://github.com/stein2nd/s2j-webinar/blob/main/docs_mod/specs.md) です。イベントの画面と投稿は [GatherPress](https://gatherpress.org/) のフォーク [stein2nd/gatherpress](https://github.com/stein2nd/gatherpress) が持ちます。

本ライブラリは **WordPress 非依存** です。GatherPress も知りません。WP フック、設定画面、OAuth トークンの保存、HTTP の実行は扱いません。

KIS2026の索引で仮称だった `s2j-◯◯◯◯` は、この1本ではありません。後継は次の3つです。

| 層 | 名称 | 状態 |
| --- | --- | --- |
| イベント UI | GatherPress フォーク | [stein2nd/gatherpress](https://github.com/stein2nd/gatherpress)。上流 `develop` に追従する |
| 呼び出し側 | [S2J Webinar](https://github.com/stein2nd/s2j-webinar) | WP プラグイン。仕様ドラフト |
| 計算 | **本ライブラリ** | [s2j-webinar-service](https://github.com/stein2nd/s2j-webinar-service) |

`kis-event-manager` の置き換え先は、この組み合わせです。

## 背景

勤務先では、Zoom Webinar でセミナーを開催しています。案内ページは、都度、manual で書き起こしてます。

検討は、次の問いから始まりました。「イベント管理の画面は、GatherPress に任せ、Webinar の作成と更新を WordPress の管理画面 (GatherPress のコンパニオン・プラグイン) からできないものか ?」

Zoom Webinar のライセンスを持つログインは、WordPress 管理者とは別です。現行では、そのユーザーで Zoom に入り、Webinar を作り、登壇者を複数登録します。

作成時には、質問メールの送信先を指定します。送信先は、登壇者リストの1人目 (社内の営業メンバー) の社内メールアドレスです。ライセンスを持つホスト (Webinar 権限のある Zoom アカウント) のメールアドレスは、営業のファンクションアドレス (たとえば `business@…`) の受信者に入っていません。質問メールは、ホストのアドレスにも、そのファンクションアドレスにも届きません。受け取る本人の社内メールアドレスを、その都度指定します。

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

GatherPress 単体も、オンライン会場の URL を書くところまで、です。Webinar の作成はしません。

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

質問メールの送信先は、セッション中の Q&A (`settings.question_and_answer`) とは別です。作成時に指定する連絡先であり、API では登録の連絡先 `settings.contact_name` と `settings.contact_email` に載せます。値は登壇者の1人目の氏名と社内メールアドレスです。ホスト (Webinar 権限のある Zoom アカウント) のメールアドレスと、営業のファンクションアドレスは使いません。セッション中の Q&A は、作成リクエストでネスト一式を送ります。パネルには出しません。

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
| Panelist 削除 | `DELETE /webinars/{webinarId}/panelists/{panelistId}` | ○ |

Panelist の削除は、外した人ごとに1回です。`panelistId` には、その人のメールアドレスを載せます。保存しているのは氏名とメールであり、差分もメールで取るためです。スコープは `webinar:delete:panelist` です。全員を消す `DELETE /webinars/{webinarId}/panelists` は使いません。残る人まで外れ、招待がやり直されるためです。同じメールで氏名だけ変わったときは、そのメールを1人用の削除で外し、追加し直します。Zoom に登壇者の更新メソッドがないためです。並び替えだけでは削除しません。1人目が変わったときの連絡先は、これまでどおり `settings.contact_name` と `settings.contact_email` です。この削除は、予定されている Webinar への反映です。開催中の部屋の役割は、ホストが Zoom の画面で扱います。

Webinar の `type` は、単発の `5` だけを初版で使います。繰り返し (`6` / `9`) は使いません。

録画は、`settings.auto_recording` です。値は `local` / `cloud` / `none` です。`cloud` のファイルは Zoom に残します。

新規登録は、同じ操作の中で二段階です。第一は `POST /users/{userId}/webinars` で、スケジュール画面の入力を送ります。ウェビナー ID は、その応答の `id` です。発行の通知は購読しません。`webinar.created` は待ちません。開催の `webinar.started` と `webinar.ended` とは別です。ID は、追加リクエストの前に結果へ含めます。参加 URL (`join_url`) も、この応答から結果に含めます。第二は、その ID をパスに含む追加リクエストで、スケジュール後のタブの初期値を送ります。詳細タブは作成結果の表示であり、書き込みはありません。追加の中身は、項目表にあるものだけです。追加が失敗しても Webinar は削除しません。ID は残します。

開始 URL (`start_url`) は、ホストがその Webinar を始めるためのリンクです。参加 URL とは別です。通常のユーザーでは、作成または `GET /webinars/{webinarId}` の応答から2時間で無効になります。接続するホスト (Webinar 権限のある Zoom アカウント) のメールアドレスは、すでにいるライセンスユーザーですので、この2時間です。90日になるのは、API の `custCreate` で作ったユーザーだけです。期限が切れても Webinar 自体は終わりません。

「Zoom で開く」は、押したときに `GET /webinars/{webinarId}` を呼び、返った `start_url` をその場で開きます。スコープは `webinar:read:webinar` です。開始 URL は保存しません。期限の記録も持ちません。2時間をタイマーやキャッシュにしません。恒久リンクとして案内に使いません。

作成で省略したとき、ユーザーのアカウント設定を継ぐと Zoom が公式にしているのは、2026年3月15日以降、次の7つです。`password`、`add_watermark`、`add_audio_watermark`、`language_interpretation`、`sign_language_interpretation`、`panelist_authentication`、`allow_host_control_participant_mute_state`。これらは省略したままにし、WordPress には持ちません。`default_password` は、この変更の対象外です。

音声、待機室、チャット、投票、テンプレート、ブランディングは、継承する7項目にありません。待機室のアップロード、投票、テンプレート、ブランディングは初版では送りません。チャットのデフォルト対象は、作成と更新のリクエストに載せません。Create a Webinar に、画面の「誰とチャットできるか」と一対一のフィールドがないためです。アカウントまたは Zoom 画面のデフォルトに任せます。オーディオは `voip`、ホストとパネリストのカメラは `false`、HD は `settings.hd_video` = `false` を、作成リクエストで送ります。更新で送るのも、本ライブラリが持つ項目だけです。設定の塊を部分的には送りません。

公式の入口は、下記です。

* [Webinars](https://developers.zoom.us/docs/api/rest/reference/zoom-api/methods/#tag/Webinars)
* [OAuth](https://developers.zoom.us/docs/integrations/oauth/)

スコープは、本人用のグラニュラーだけです。末尾が `:admin` のものは要求しません。アプリはユーザー管理の OAuth であり、公開マーケットには出さず、サーバー間認証にもしません。認可 URL の材料には、次だけを含めます。

| 操作 | スコープ |
| --- | --- |
| 作成 | `webinar:write:webinar` |
| 取得 | `webinar:read:webinar` |
| 更新 | `webinar:update:webinar` |
| 削除 | `webinar:delete:webinar` |
| 登壇者の追加 | `webinar:write:panelist` |
| 登壇者の削除 | `webinar:delete:panelist` |
| 接続ユーザーのメール | `user:read:user` |

リダイレクト URL とトークンの保存は、呼び出し側です。詳細はプラグイン仕様です。

## フィールドの突き合わせ (実装の前提)

`zoom3` の「ウェビナーをスケジュール」画面で毎回入れる項目は、突き合わせ済みです。会社が使わない項目は、画面に出しません。トピックは200文字まで、説明は2000文字まで、です。空の説明は送りません。

作成リクエストで送る項目は、次です。

| 項目 | 送り先 |
| --- | --- |
| タイトル | `topic`。200文字まで |
| 概要 | `agenda`。空なら送らない。2000文字まで |
| 開始日時 | `start_time` |
| タイムゾーン | `timezone`。イベントの `gatherpress_timezone`。パネルには出さない |
| 所要時間 (分) | `duration` |
| 種別 | `type` = `5`。定期開催は送らない |
| 録画 | `settings.auto_recording`。デフォルトは `cloud` |
| オーディオ | `settings.audio` = `voip`。電話の項目は送らない |
| ホストのカメラ | `settings.host_video` = `false` |
| パネリストのカメラ | `settings.panelists_video` = `false` |
| HD | `settings.hd_video` = `false`。画面の「HD ビデオ品質」のデフォルト no に合わせる |
| 出席者の参加時認証 | `settings.meeting_authentication` = `false`。`authentication_option` と `authentication_domains` は送らない |
| セッション中の Q&A | `settings.question_and_answer`。`enable` = `true`、`allow_anonymous_questions` = `true`、`answer_questions` = `only`。`enable` だけでは送らない。コメントと upvote は画面で毎回触っていないので送らない |
| 質問メールの送信先 (1人目の氏名と社内メール) | `settings.contact_name` / `settings.contact_email` |
| 参加登録 | `settings.approval_type`。`0` 必須・自動承認 (デフォルト) / `1` 必須・手動承認 / `2` 不要。省略しない |
| 登壇者の氏名とメール | 作成直後の Panelist 追加 |

パスコード (`password`) は送りません。公開ページにも出しません。必須かつロックされているとき、Zoom が応答で自動生成します。最大10文字で、使えるのは英数字と `@ - _ * !` です。パネリストの参加時認証 (`panelist_authentication`) も送りません。継承する7項目です。出席者の参加時認証とは別です。古い `enforce_login` は使いません。代替ホスト、オンデマンド、国・地域の制限は送りません。チャットのデフォルト対象も送りません。作成 API に一対一のフィールドがありません。

スケジュール後のタブは、この表には入れません。作成応答の `id` を受け取った直後の追加リクエストで、項目が決まっているものだけ初期セットします。詳細タブは作成結果の表示であり、書き込みはありません。

### 項目「参加登録」

参加登録は、呼び出し側がイベントごとに選んだ値です。未指定のときのデフォルトは `0` (必須・自動承認) です。`zoom3` の作成画面が登録必須だからです。イベントページを一つの参加 URL にする回は `2` (不要) を選びます。作成と更新では、その値を `settings.approval_type` に含め、省略しません。省略すると、接続ユーザーのデフォルトが使われる可能性があります。

`2` では、イベントページの一つの `join_url` から入れます。Zoom は入室時に氏名とメールアドレスを尋ねます。

`0` と `1` では、その `join_url` は Zoom の登録ページに回され、承認された人が自分用の URL を受け取ります。申込者を registrant として送ることは、初版の外です。

## Composer ライブラリの理由

見た目は WordPress のイベント編集画面ですが、層は次に分かれます。

| 層 | 中身 | 置き場 |
| --- | --- | --- |
| 計算 | Webinar 項目の検証、作成・更新・削除・再取得のリクエスト材料、Panelist の差分、API 応答から結果レコードへの写像 | **本ライブラリ** |
| 副作用 | OAuth、トークン保存、HTTP、GatherPress のメタ、管理画面 | **S2J Webinar** (プラグイン) |
| イベントの正 | タイトル、日時、公開 URL、登壇者の順序 | **GatherPress** |

プラグイン一本に判断を置くと、日時と録画方式の組み合わせをユニットテストするたびに WordPress と Zoom が必要になります。

## 本ライブラリの責務

入力は、Webinar レコードと「今」の時刻です。出力は、次の操作と、操作後のレコードです。HTTP レスポンスの解釈も、渡されたステータスとボディから、結果レコードを作るところまで、です。

| 責務 | 内容 |
| --- | --- |
| 検証 | タイトルは空でなく、200文字まで。説明は2000文字まで。開始はタイムゾーン付き。所要時間の単位は分 (マイナスではない)。`auto_recording` は3値のいずれかで、なければ `cloud`。`approval_type` は `0` / `1` / `2` のいずれかで、なければ `0`。登壇者は1人以上で、各自が氏名とメールを持つ。1人目の氏名と社内メールを質問メールの送信先にする |
| 次の操作 | `create` / `update` / `delete` / `get` / `add_panelists` / `remove_panelists` / `none` |
| リクエスト材料 | メソッド、パス、ボディ。`Authorization` は含めない。プラグインがトークンを付ける |
| OAuth の材料 | 認可 URL、認可コード交換、リフレッシュのリクエスト材料。トークンは保存しない |
| Panelist の差分 | 前回と今回のメールで、追加と削除を分ける。氏名だけ変わったメールは、削除してから追加する。並び替えだけでは削除しない。削除のパスは `DELETE /webinars/{webinarId}/panelists/{panelistId}` で、`panelistId` はメール |
| 結果の写像 | 成功で Webinar ID、UUID、`join_url`、ホストを埋める。失敗では ID を空のままにし、エラー文を残す。トークンとクライアントシークレットは残さない |

質問メールの送信先は、独立した担当者フィールドにはしません。登壇者リストの1人目から取り、作成と更新のリクエストに含めます。1人目が変わったときも、Webinar の連絡先を更新します。

OAuth のクライアント ID とクライアント・シークレットは、ライブラリに埋め込みません。プラグインがサイト設定から読み、実行時に渡します。リポジトリにコミットしません。

## 状態

レコードが持つ時刻は、タイムゾーン付きの瞬間です。画面の日付と時刻は、WordPress「設定 > 一般」のタイムゾーンで出します。ライブラリは表示文字列を作りません。

| 項目 | 備考 |
| --- | --- |
| provider | 初版は `zoom` のみ |
| topic | |
| agenda | 空可 |
| start_at | |
| timezone | `IANA` |
| duration_minutes | |
| auto_recording | `none` / `cloud` / `local` |
| approval_type | 0 / 1 / 2。デフォルトは0 (必須・自動承認) |
| panelists | 順序あり。`name` と `email`。1人目が質問メールの送信先 |
| webinar_id | 未作成なら空 |
| webinar_uuid | 空可 |
| join_url | 空可 |
| status | `not_created` / `synced` / `dirty` / `error` |
| last_error | 空、または直近の失敗。トークンは入れない |

`status` の意味は、次のとおりです。

| status | 意味 |
| --- | --- |
| `not_created` | Webinar ID が空 |
| `synced` | 直近の作成または更新が成功し、そのとき送った項目から WordPress 側が変わっていない |
| `dirty` | 成功のあと、WordPress 側の項目が変わった。まだ Zoom に送っていない |
| `error` | 直近の API が失敗した。ID は、作成前なら空のまま |

初版は一方向です。WordPress から Zoom に作成・更新します。Zoom で直接直したタイトルや日時は検知して取り込みません。開催の開始と終了だけは、呼び出し側が `webinar.started` と `webinar.ended` で受けます。再取得は表示用であり、WordPress のタイトルや日時を Zoom の値で上書きしません。

同じ項目での更新を、成功のたびに無条件で繰り返しません。`dirty` のときだけ `update` を返します。

## プラグインの責務 (境界。詳細はプラグイン仕様)

プラグイン仕様の詳細は [S2J Webinar の docs_mod/specs.md](https://github.com/stein2nd/s2j-webinar/blob/main/docs_mod/specs.md) です。ここには境界だけを置きます。

| 責務 | 内容 |
| --- | --- |
| 接続 | 管理画面の「Zoom と接続」。Webinar 権限のあるアカウントで許可し、リフレッシュ・トークンをサイト設定に保存する。WordPress 管理者とは別人でよい |
| 実行 | 本ライブラリが返したリクエストだけを Zoom に送る |
| 関連 | GatherPress のイベントに、プロバイダ、Webinar ID、UUID、`join_url`、`status` を保存する。プロバイダの値はコードが `zoom` と書く。公開の参加 URL は `Event::set_online` で `gatherpress_online_event_link` に書く。1イベントにつき Webinar は1つ |
| 日時 | `gatherpress_datetime_start`、`gatherpress_datetime_end`、`gatherpress_timezone` を読む。`duration_minutes` は開始と終了の差 (分)。JSON の `gatherpress_datetime` と GMT のキーは渡さない。終日は、開始 `0:00` と暦日数×1440分を渡して初版から登録する。Zoom に終日フラグはない |
| 登壇者 | 順序付きで保存する。1人目は質問メールを受け取る営業メンバーで、社内メールアドレスを持つ。Zoom には Panelist として渡し、同じ氏名とメールを登録の連絡先にも載せる |
| 画面 | 接続状態、未作成 / 同期済み / 未同期 / 失敗、開催 (未開始 / 開催中 / 終了)、録画方式 (デフォルトはクラウド)、参加登録 (ラジオ。デフォルトは必須・自動承認)、新規登録、更新、削除、再取得、Zoom で開く |
| 開催 | `webinar.started` と `webinar.ended` を受ける。終了で公開の参加 URL を空にする。ページ表示のたびに Zoom には問い合わせない |
| 削除 | パネルの明示操作だけ。ゴミ箱への移動と完全削除 (30日後の自動削除を含む) では Zoom を削除しない |
| 効果測定 | 数値は持たない。必要なら Zoom のダッシュボードへのリンクだけ |

GatherPress のフォークには、この画面のコードを入れません。

### 後続 (プラグイン。本ライブラリの外)

イベント公開の成果物は、Zoom 連携のあとでプラグイン側に足します。

* CoverArt は、メディアライブラリの添付であり、イベントの資産です。Zoom のサムネイルにコピーするのは、その後の任意です。
* QR は、本ライブラリの外です。後続の QR コード・ジェネレータが WordPress 内で作り、qr.quel.jp は使いません。飛び先は GatherPress のイベント URL です。`utm_medium` は `qr`、`utm_campaign` はイベントのスラッグ、`utm_source` はユーザーが管理する用語で、新規のデフォルトはブランクです。用語集は S2J Webinar の設定には置きません。色、中央のアイコン、SVG と PNG は、そのジェネレータの仕事です。モジュールは四角に固定します。詳細はプラグイン仕様です。
* フライヤー PDF と配配メール用のヘッダーは、CoverArt と QR がそろってから検討します。配配メールへの送信そのものは、このプラグインの外です。
* 公開前チェックリスト (Webinar、CoverArt、QR、フライヤー) は、プラグインの表示です。
* 招待状のソース追跡は、QR の `utm_source` と同じ用語です。登録ページのバナーは CoverArt ができてから、登録ページを使うときに出します。
* アンケートの設問は、[S2J Webinar Survey Service](https://github.com/stein2nd/s2j-webinar-survey-service) が検査します。仕様は [docs_mod/service_spec.md](https://github.com/stein2nd/s2j-webinar-survey-service/blob/main/docs_mod/service_spec.md) です。本ライブラリは、作成直後に、使ってよいと判定された文書を `PATCH /webinars/{webinarId}/survey` の材料に写す役です。設問の文面と助言は持ちません。写像の詳細は下記です。
* 登壇者のプロフィール台帳、確認メールとリマインダーの「最新情報」、出席者・欠席者メールの「末尾の告知」、待機室の画像と動画、投票の流用、ライブストリームの URL とキー、チャットのデフォルト対象、登録者数の Slack や LineWorks への通知は、それぞれの API がイベント単位で受け付けると分かってから足します。連携タブの他製品接続は、本ライブラリに入れません。

## アンケート添付の写像

操作は `PATCH /webinars/{webinarId}/survey` (`webinarSurveyUpdate`) です。投票 (poll) と登録の質問は使いません。`GET /report/webinars/{webinarId}/survey` は回答レポートであり、設問の作成・更新ではありません。

`custom_survey.questions[].type` の enum は次です。初版で送るのは先頭の5つだけです。

| Zoom UI | API `type` | 初版 |
| --- | --- | --- |
| 単一選択 | `single` | 送る |
| 複数選択 | `multiple` | 送る |
| 短い回答 | `short_answer` | 送る |
| 長い回答 | `long_answer` | 送る |
| レーティングスケール | `rating_scale` | 送る |
| マッチング | `matching` | 送らない |
| ランク順 | `rank_order` | 送らない |
| 空欄に記入する | `fill_in_the_blank` | 送らない |

文書から Zoom へのキーの写像は、次のとおりです。

| 文書 | Zoom |
| --- | --- |
| `prompt` | `name` |
| `required` | `answer_required` |
| `single` + `choices` | `type` = `single`、`answers` = `choices` |
| `multiple` + `choices` | `type` = `multiple`、`answers` = `choices` |
| `short` | `type` = `short_answer`。`answer_min_character` / `answer_max_character` は送らない (API の既定) |
| `long` | `type` = `long_answer`。文字数は同上 |
| `rating` の `score_min` | `rating_min_value` |
| `rating` の `score_max` | `rating_max_value` |
| `rating` の `label_low` | `rating_min_label` |
| `rating` の `label_high` | `rating_max_label` |
| `internal_name` | 調査の内部名まわり。設問の `type` ではない |
| (未設定の見出し) | `custom_survey.title` はウェビナータイトル。説明は空 |

`show_as_dropdown`、重み、画像、スキップロジックは送りません。画像、スキップ、`matching`、`rank_order`、`fill_in_the_blank` は初版以降の検討です。

## 設計方針

本ライブラリは [kis-wordpress エコシステム仕様](https://github.com/stein2nd/kis-wordpress/blob/main/docs_mod/specs.md) と同じく、FOP + Clean Coding を基本とします。Clean Architecture の定型分割は採用しません。

データの中身は、純粋関数と不変レコードに閉じます。接続先は、プロバイダ名から関数を引く表です。今実装するのは Zoom だけです。使わない接続先の空実装は置きません。プロバイダ名は、呼び出し側がコードで `zoom` と渡します。管理画面の入力にはしません。

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
2. 本 repo でスケルトンと純関数の初版 (PHPUnit、WordPress なし、HTTP なし)。
3. プラグインが Composer で require する。OAuth 接続と、GatherPress イベントからの Webinar 作成・結果の保存をつなぐ。
4. 更新、削除、再取得、Panelist の差分、録画方式を足す。
5. CoverArt、QR、フライヤー、公開チェックリストは、プラグインの後続仕様で扱う。

## 本ドラフトの提案

合意前の提案です。

* 製品のコアは、GatherPress のイベントから Zoom Webinar を一方向に作ることである。
* 計算は本ライブラリ、接続と保存は S2J Webinar、イベントの画面は GatherPress フォークである。フォーク本体は改変しない。
* ホストは、OAuth で接続した Zoom ユーザーであり、WordPress 管理者ではない。
* 初版の API は、Webinar の作成、取得、更新、削除と、Panelist の追加・削除である。Meeting は、作らない。
* 新規登録は二段階である。作成応答の `id` がウェビナー ID であり、発行は購読しない。その ID で、スケジュール後のタブの初期値を追加リクエストする。
* Panelist の削除は `DELETE /webinars/{webinarId}/panelists/{panelistId}` である。`panelistId` は外した人のメールである。全員削除は使わない。
* 録画方式は、`none` / `cloud` / `local`。デフォルトは `cloud`。ファイルは、Zoom に置く。
* 質問メールの送信先は、登壇者の1人目の氏名と社内メールアドレスである。登録の連絡先として Zoom に送る。ホストと営業ファンクションアドレスは使わない。
* オーディオは `voip`。ホストとパネリストのカメラは `false`。HD は `settings.hd_video` = `false`。出席者の参加時認証は `settings.meeting_authentication` = `false`。パスコードは送らない。
* セッション中の Q&A は、作成リクエストで `settings.question_and_answer` の一式を送る。`enable` = `true`、`allow_anonymous_questions` = `true`、`answer_questions` = `only`。`enable` だけでは送らない。コメントと upvote は送らない。
* チャットのデフォルト対象は、作成と更新では送らない。Create a Webinar に一対一のフィールドがない。イベント単位で API が受け付けると分かってから足す。
* パネリストの参加時認証 (`panelist_authentication`) は送らない。継承する7項目である。`enforce_login` は使わない。
* 参加登録はイベントごとに選ぶ。デフォルトは必須・自動承認 (`approval_type` = `0`)。イベントページを一つの参加 URL にする回は `2`。選んだ値は省略せず送る。
* 作成で省略してアカウント設定を継ぐのは、Zoom が公式に挙げた7項目だけである。`password`、`add_watermark`、`add_audio_watermark`、`language_interpretation`、`sign_language_interpretation`、`panelist_authentication`、`allow_host_control_participant_mute_state`。WordPress には持たない。更新は、本ライブラリが持つ項目だけを送る。
* 同期は、WordPress から Zoom への一方向である。開始と終了だけは `webinar.started` と `webinar.ended` で受ける。再取得は、表示用であり、イベントの項目を上書きしない。
* ゴミ箱への移動と完全削除では Zoom を削除しない。Zoom の削除は、パネルの明示操作だけである。
* 効果測定、申込者の registrant 登録、CoverArt、QR、フライヤーは、初版の外である。
* OAuth クライアントは、サイト設定であり、配布物に含めない。
* ライセンスは、プラグインとライブラリの両方で GPL-3.0-or-later。
* パッケージ名は `s2j/webinar-service`。プラグインのスラッグは `s2j-webinar`。
* プロバイダはコードのアダプタである。初版は `zoom` だけを実装し、管理画面では選ばせない。
* OAuth はユーザー管理アプリである。スコープは本人用のグラニュラーだけを要求する。
* 開始 URL (`start_url`) は保存しない。「Zoom で開く」は、押したときに `GET /webinars/{webinarId}` の `start_url` をその場で開く。通常ユーザーの期限は2時間であり、タイマーにはしない。
* 開始・終了・タイムゾーンは GatherPress の `gatherpress_datetime_start`、`gatherpress_datetime_end`、`gatherpress_timezone` から渡される。所要時間はその差 (分) である。終日は開始 `0:00` と暦日数×1440分で、初版から登録する。

## 改訂履歴

| 日付 | 内容 |
| --- | --- |
| 2026-10-03 | 初版ドラフト。GatherPress をイベント UI、本ライブラリを Zoom Webinar のリクエスト組立、呼び出し側プラグインを未作成のコンパニオンとする。一方向同期、録画は Zoom、質問受け付けはイベントのメタデータ、QR と CoverArt は後続、と記録 |
| 2026-10-03 | 質問受け付けを、質問メールの送信先に改めた。登壇者1人目の社内メールを `contact_name` / `contact_email` で Zoom に送る。ホストと営業ファンクションアドレスは使わない |
| 2026-10-03 | 呼び出し側プラグイン [s2j-webinar](https://github.com/stein2nd/s2j-webinar) のリポジトリができた。仕様は当該 repo の `docs_mod/specs.md` |
| 2026-10-05 | 参加登録は不要とする。作成と更新で `settings.approval_type` に `2` を送り、省略しない、と記録 |
| 2026-10-05 | 参加登録はイベントごとに選ぶ。デフォルトは `2`。選んだ値を省略せず送る、と記録 |
| 2026-10-05 | ゴミ箱への移動と完全削除では Zoom を削除しない。Zoom の削除はパネルの明示操作だけである、と記録 |
| 2026-10-05 | セッション中の Q&A は初版では送らない。接続ユーザーのデフォルトに任せる、と記録 |
| 2026-10-05 | QR の `utm_source` はユーザー管理 (デフォルトはブランク)。用語集は後続の QR コード・ジェネレータが持ち、本ライブラリと S2J Webinar の設定には置かない、と記録 |
| 2026-10-05 | プロバイダ名は呼び出し側がコードで `zoom` と渡す。管理画面の入力にはしない、と記録 |
| 2026-10-05 | OAuth はユーザー管理アプリとする。スコープは本人用のグラニュラーだけを認可 URL の材料に含める、と記録 |
| 2026-10-05 | 公開の参加 URL は `Event::set_online` で `gatherpress_online_event_link` に書く、と記録 |
| 2026-10-05 | 開催の開始と終了は `webinar.started` と `webinar.ended` で受ける。タイトルと日時の一方向はそのまま、と記録 |
| 2026-10-05 | 日時は `gatherpress_datetime_start`、`gatherpress_datetime_end`、`gatherpress_timezone` から渡す。所要時間はその差 (分)。終日は本ライブラリを呼ばない、と記録 |
| 2026-10-05 | 終日も初版で登録する。開始は `0:00`、所要時間は暦日数×1440分。Zoom に終日フラグはない、と記録 |
| 2026-10-05 | QR のモジュールは四角に固定する。形状の選択は出さない、と記録 |
| 2026-10-05 | Panelist の削除は `DELETE /webinars/{webinarId}/panelists/{panelistId}` とする。`panelistId` はメール。全員削除は使わない、と記録 |
| 2026-10-05 | 開始 URL は保存しない。「Zoom で開く」は押したときに `GET /webinars/{webinarId}` の `start_url` を開く。通常ユーザーの期限は2時間で、タイマーにはしない、と記録 |
| 2026-10-05 | 作成で省略してアカウント設定を継ぐのは、Zoom が公式に挙げた7項目だけとする。それ以外はフィールドごと送らず、更新は本ライブラリの項目だけ送る、と記録 |
| 2026-10-06 | 新規登録は二段階とする。ウェビナー ID は作成応答の `id` であり、発行は購読しない。その ID でタブの初期値を追加リクエストする、と記録 |
| 2026-10-06 | `zoom3` のスケジュール画面の項目表を確定する。参加登録のデフォルトは必須・自動承認。録画のデフォルトはクラウド。トピックは200文字、説明は2000文字。パスコードは送らない、と記録 |
| 2026-10-06 | アンケート設問の検査は [s2j-webinar-survey-service](https://github.com/stein2nd/s2j-webinar-survey-service) に分けた、と記録 |
| 2026-10-07 | Q&A は作成で `question_and_answer` 一式を送る。HD は `hd_video` = `false`。チャットのデフォルト対象は送らない、と記録 |
| 2026-10-07 | 出席者の参加時認証は `settings.meeting_authentication` = `false`。`panelist_authentication` と `enforce_login` は使わない、と記録 |
| 2026-10-07 | アンケート添付の写像は本ライブラリが持つ。初版は single / multiple / short / long / rating。公式 webinar survey 更新で型名確定まで添付しない。画像・スキップ・マッチング・ランク・空欄記入は初版以降の検討、と記録 |
| 2026-10-07 | 初版5種の `type` を確定 (`single` / `multiple` / `short_answer` / `long_answer` / `rating_scale`)。文書から `name` / `answer_required` / `answers` / `rating_*` への写像表を記録。Report API は回答用、と記録 |
