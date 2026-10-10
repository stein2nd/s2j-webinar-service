<!--
目的：「次の操作の計画」の明文化
-->

# S2J Webinar Service - 操作計画の仕様

本ドキュメントは、レコードから **次に行う操作の列** を決める規則を定義します。

## 責務

* 操作名と、いつそれを返すかを定義すること。
* 計画用コンテキストと、返す `operations[]` の形を定義すること。

## 非責務

* リクエストボディの中身 ([request_spec.md](./request_spec.md))
* Panelist 差分の詳細規則 ([panelist_spec.md](./panelist_spec.md))。本計画は同規則を内部で使う
* HTTP の実行順のオーケストレーション (プラグイン。ただし推奨順は下記)

## 操作名

本節は **操作の意味** だけを定義します。REST パス・ボディの正本は各プロバイダ記述子です (初版 Zoom は [request_spec.md](./request_spec.md))。同名 `op` でも接続先ごとにパスが異なりうます。

| 操作 | 意味 |
| --- | --- |
| `create` | Webinar を新規作成する |
| `update` | 既存 Webinar の本体項目を更新する |
| `delete` | 既存 Webinar を削除する |
| `get` | 既存 Webinar を再取得する (表示用) |
| `add_panelists` | 登壇者を追加する |
| `remove_panelists` | 登壇者を1人ずつ外す (1メール=1操作要素) |
| `attach_survey` | アンケートを Webinar に添付する |

何もしない場合は `operations` を **空配列** にします。`none` という操作名は返しません。

## 計画用コンテキスト

`plan_webinar_operations( $record, $context )` の `$context` キーです。未指定は偽 / 空と同じです。

| キー | 型 | 意味 |
| --- | --- | --- |
| `previous_panelists` | `{name?,email?}[]` | 前回送った (または保持している) 登壇者。差分の比較元 |
| `intend_delete` | bool | 明示の削除要求 |
| `intend_get` | bool | 明示の再取得要求 (表示用) |
| `intend_retry` | bool | `error` からの再試行要求 |
| `survey_document` | object\|null | `ready` と判定済みの設問文書。**非空なら添付意図** (初版。別キー `intend_attach_survey` は置かない) |
| `webinar_title` | string | survey 見出し未設定時の title 用 |

survey 未完了を create の条件にしません。文書がまだなければ Panelist まで計画し、後から `survey_document` を渡せば同じパイプラインで `attach_survey` だけを計画します。

## `operations[]` の形

各要素は最低 `op` を持ちます。`build_webinar_request( $step['op'], $record, $step )` に渡せば足りる形にします。

| `op` | 追加キー | 説明 |
| --- | --- | --- |
| `create` / `update` / `delete` / `get` | (なし) | レコードから材料を組む |
| `add_panelists` | `panelists` | 追加する `{ name, email }[]` (差分の `add`) |
| `remove_panelists` | `email` | **1メール=1要素** (正規化後)。削除 URL への載せ方は記述子正本 (初版 Zoom: [panelist_spec.md](./panelist_spec.md) / [request_spec.md](./request_spec.md)) |
| `attach_survey` | `survey_document` (必須)。`webinar_title` (任意) | **要素に必ず載せる。** `build_webinar_request( …, $step )` だけで足りる形にする。計画時 context を build に持ち回さない。`webinar_title` 欠落時はレコードの `topic` にフォールバックしてよい |

`remove` が複数ある場合は、削除用要素を並べたあと `add_panelists` を1つ付けます (氏名のみ変更は削除→追加の順)。

### 列の順 (複数 op が同時にある場合)

1. 本体: `create` または `update` (ある場合)  
2. `remove_panelists` (メールごと)  
3. `add_panelists`  
4. `attach_survey`  
5. `get` (`intend_get` の場合。書き込み列の末尾)

`delete` だけの列では `get` を付けません (削除後に取得しない)。`intend_get` だけの列はこの順の対象外です。

### `intend_get` の付け方 (共通)

書き込み系の分岐 (手順2〜5) のあと、`intend_get` が真なら列末尾に `get` を付けます。

* id がすでに非空なら、その id 向け。
* 手順2 (create) では計画に `get` を含めてよい。**実行は create 成功後** (panelist / survey と同じ)。

## 計画の規則 (初版)

入力: **呼び出し側が正規化・検証済み (不足ゼロ)** のレコードと上記コンテキストです。本関数は `validate` を再実行しません。validate を飛ばした場合の書き込み列の正しさは保証しません。

同じ項目での更新を、成功のたびに無条件で繰り返しません。`dirty` の場合だけ `update` を返します。

呼び出し側への注記: 本体項目 (topic / 日時 / 録画 / 参加登録等) が変わった場合だけ `dirty` にする。**登壇者だけ・アンケートだけの変更は `synced` のままでよい** (下記の synced 分岐が差分と `attach_survey` を扱う)。不足がある場合は書き込み用の `plan` を呼ばない (表示用は `intend_get` + id 非空でよい)。

Panelist 差分は、本関数が `previous_panelists` とレコードの `panelists` から [panelist_spec.md](./panelist_spec.md) と同じ規則で計算します (公開 `diff_panelists` と二重実装しない)。

1. **`intend_delete` が真**  
   * `webinar_id` が非空 → `delete`。空 → 空列。  
   * ゴミ箱移動・完全削除ではプラグインが本ライブラリの delete を呼ばない (境界。プラグイン仕様)。  
   * **`get` は付けない** (上記の例外)。
2. **`webinar_id` が空** (`not_created` / 作成失敗の `error` を含む)  
   * `create`。続けて Panelist 追加が必要なら `add_panelists` を列に含める (実行は create 成功後)。  
   * `survey_document` が非空なら `attach_survey` も列に含める (実行は create 成功後。文書の `ready` 判定はプラグイン / Survey Service)。  
   * `intend_get` なら末尾に `get` (実行は create 成功後)。
3. **`status` が `dirty`** かつ `webinar_id` 非空  
   * `update`。1人目変更に伴う連絡先更新は update ボディに含める。  
   * Panelist 差分があれば `remove_panelists` / `add_panelists` を続ける。  
   * `survey_document` が非空なら `attach_survey` を続ける。  
   * `intend_get` なら末尾に `get`。
4. **`status` が `synced`** かつ `webinar_id` 非空  
   * 同じ項目での無条件 `update` は返さない。  
   * Panelist 差分のみ、および / または `survey_document` 非空なら `attach_survey`。  
   * `intend_get` なら末尾に `get`。いずれもなければ空列。
5. **`status` が `error`**  
   * **`intend_retry` が真** かつ `webinar_id` 非空: **手順3 (`dirty` 分岐) と同じ計画**を返す。`dirty` フラグは見ない (`error` のまま再試行できる)。  
     * `update`。1人目変更に伴う連絡先更新は update ボディに含める。  
     * Panelist 差分があれば `remove_panelists` / `add_panelists` を続ける。  
     * `survey_document` が非空なら `attach_survey` を続ける。  
     * `intend_get` なら末尾に `get`。  
   * **`intend_retry` が偽** かつ id 非空: `intend_get` なら `get`、それ以外は空列。  
   * id 空の作成失敗は手順2 (id 空) で `create` できる。**`intend_retry` は不要** (付けても害はない)。

分岐は上から順です。`webinar_id` 空は `status` より先に見ます (正規化は [record_spec.md](./record_spec.md)。id 空でも作成失敗の `error` は維持しうる)。

## 新規登録の実行順 (プラグイン向け)

追加が失敗しても Webinar は削除しません。ID は残します。survey 未完了を create の条件にしません。

1. `create` を送る。  
2. 応答を写像し `webinar_id` を得る。  
3. `add_panelists` を送る (計画にあれば)。  
4. `attach_survey` を送る (計画にあれば。ready 文書が後から来た場合は、別呼び出しの計画で本ステップだけを行う)。

## 関連

* Panelist: [panelist_spec.md](./panelist_spec.md)
* リクエスト: [request_spec.md](./request_spec.md)
* PHP API: [../interfaces/php_api_spec.md](../interfaces/php_api_spec.md)
