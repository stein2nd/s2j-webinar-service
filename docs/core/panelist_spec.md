<!--
目的：「Panelist 差分」の明文化
-->

# S2J Webinar Service - Panelist 仕様

## 責務

* 前回と今回の登壇者リストから、追加・削除を分けること。
* メールの正規化規則を定義すること。

## 非責務

* Zoom への送信
* 開催中ルームの役割変更 (ホストが Zoom 画面で扱う)

## メールの正規化 (初版)

**適用箇所は差分と比較と、リクエスト材料の組立だけです。** `normalize_webinar_record` はレコード上の `email` を小文字に書き戻しません (呼び出し側の表示・メタの形を保つ)。

差分の比較キーと、add / remove のリクエストに載せる `email` には、同じ正規化を使います。

1. 前後の空白を trim する。  
2. **ASCII 英字を小文字にそろえる** (ロケール依存の変換は使わない。テストで固定する)。

氏名 (`name`) は trim のみとし、大小は変えない。検証の空判定は trim 後でよい ([validation_spec.md](./validation_spec.md))。

## 識別子

* 差分のキーは **正規化後のメール** である (プロバイダ共通)。
* 保存しているのは氏名とメールである。Zoom の panelistId はレコードに持たない。
* **初版 Zoom:** 削除 URL の `{panelistId}` には正規化後の **メールアドレス** を載せる。他プロバイダは記述子側で定義する。

## 差分規則

**全員を一度に消す操作は使いません** (1メール=1削除要素)。初版 Zoom のパス正本は [request_spec.md](./request_spec.md) (`remove_panelists`)。

| 状況 | 操作 |
| --- | --- |
| 今回にあり前回にないメール (正規化後) | 追加 |
| 前回にあり今回にないメール (正規化後) | 削除 |
| 同じメールで氏名だけ変わった | 削除してから追加 |
| 並び替えだけ (正規化後の集合と各氏名が同じ) | 削除しない・追加しない |

## 出力

```text
add:    { name, email }[]   追加順は今回リストの順。email は正規化後
remove: { email }[]         削除対象メール (正規化後)
```

## 呼び出し方

* `plan_webinar_operations` が `previous_panelists` とレコードの `panelists` から、本規則で差分し、`operations` に載せます。
* 公開関数 `diff_panelists` は同じ規則のヘルパーです。規則の二重実装はしません。

## 関連

* 操作計画: [operation_spec.md](./operation_spec.md)
* リクエスト: [request_spec.md](./request_spec.md)
