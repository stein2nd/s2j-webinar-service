<!--
目的：「実装状況サマリー」の明文化
-->

# S2J Webinar Service - 実装状況

最終更新: 2026-10-13

## 仕様書 (参照元)

* [specs.md](./specs.md) — 索引
* [service_spec.md](./service_spec.md) — 統合見取り図 (要約。規則の正本ではない)
* [core/](./core/record_spec.md) — 規則 (SoT)
* [contracts/data_contract_spec.md](./contracts/data_contract_spec.md) — DTO (SoT)
* [interfaces/php_api_spec.md](./interfaces/php_api_spec.md) — 公開面

## 機能一覧

**完了条件列:** 100% 到達用チェックリスト (1行 = PHPUnit ケース1件、または CI ステップ1件)。`[x]` = 満たし済み、`[ ]` = 未。正本 spec は各 § の見出しリンク。

| 機能名 | 実装済み/未実装 | 実装％ | 完了条件 (100%) |
| --- | --- | --- | --- |
| 仕様分割 (`docs/`) | 確定 | — | [§ 仕様分割](#status-spec-docs) |
| Composer スケルトン (`src/` 公開 API) | 実装済み | 100 | [§ Composer](#status-composer) (14/14) |
| レコード正規化 + 検証 | 実装済み | 100 | [§ 正規化・検証](#status-record-validate) (12/12) |
| プロバイダ・レジストリ | 実装済み (`zoom`) | 100 | [§ レジストリ](#status-provider) (8/8) |
| 操作計画 | 実装済み | 100 | [§ 操作計画](#status-plan) (11/11) |
| リクエスト材料 (CRUD + Panelist) | 実装済み (`zoom`) | 100 | [§ リクエスト材料](#status-request) (12/12) |
| Panelist 差分 | 実装済み | 100 | [§ Panelist](#status-panelist) (6/6) |
| 結果写像 | 実装済み (`zoom`) | 100 | [§ 結果写像](#status-result) (11/11) |
| OAuth 材料 | 実装済み (`zoom`) | 100 | [§ OAuth](#status-oauth) (9/9) |
| アンケート写像 | 実装済み (`zoom`) | 100 | [§ アンケート](#status-survey) (9/9) |
| Source Tracking 取り込み | 実装済み (`zoom`) | 100 | [§ Tracking](#status-tracking) (12/12) |
| PHPUnit / PHPStan / PHPCS | 実装済み | 100 | [§ 品質ゲート](#status-quality) (7/7) |
| Packagist 公開 | 実装済み | 100 | [§ Packagist](#status-packagist) (5/5) |
| プラグインからの require | 未実装 | 0 | [§ プラグイン](#status-plugin) (0/4) |

## 100% 完了条件チェックリスト

### 仕様分割 {#status-spec-docs}

* [x] CI: `docs-lint` が `docs/**` を lint ([ci.md](./engineering/ci.md))
* [x] 正本が `docs/`、起草が `docs_mod/` ([governance/documentation_governance.md](./governance/documentation_governance.md))

### Composer スケルトン {#status-Composer}

正本: [php_api_spec.md](./interfaces/php_api_spec.md)

* [x] Test: `RecordNormalizeTest` — 公開 `normalize_webinar_record`
* [x] Test: `ValidationDeficiencyTest` — 公開 `validate_webinar_record`
* [x] Test: `OperationPlanTest` — 公開 `plan_webinar_operations`
* [x] Test: `PanelistDiffTest` — 公開 `diff_panelists`
* [x] Test: `BuildWebinarRequestTest` — 公開 `build_webinar_request`
* [x] Test: `MapWebinarResponseTest` — 公開 `map_webinar_response`
* [x] Test: `OAuthMaterialsTest` — 公開 `build_oauth_*`
* [x] Test: `TrackingImportTest` — 公開 `build_tracking_sources_request` / `map_tracking_sources_response`
* [x] Test: `SurveyHelperTest` — `build_survey_update_request` が SoT と同一写像 (`webinar_title` null / topic フォールバック)
* [x] Test: 未知 `op` の build / map が `InvalidArgumentException` (`BuildFacadeTest` / `MapWebinarResponseTest`)
* [x] CI: `php-quality` — `composer install` → PHPUnit
* [x] CI: `php-quality` — PHPStan level8
* [x] CI: `php-quality` — PHPCS 警告0 (`composer phpcs` = `phpcs -n`)
* [x] CI: `composer validate-composer` + `composer verify-dist` ([build_and_release.md](./engineering/build_and_release.md))

### レコード正規化 + 検証 {#status-record-validate}

正本: [record_spec.md](./core/record_spec.md) / [validation_spec.md](./core/validation_spec.md)

* [x] Test: `RecordNormalizeTest::testMissingScalarsDefaultPerSpec`
* [x] Test: `RecordNormalizeTest::testIdEmptyWithoutLastErrorIsNotCreated`
* [x] Test: `RecordNormalizeTest::testIdEmptyWithLastErrorStaysError`
* [x] Test: `RecordNormalizeTest::testIdNonEmptyMissingStatusBecomesDirty`
* [x] Test: `RecordNormalizeTest::testPanelistEmailCasePreservedOnRecord`
* [x] Test: `ValidationDeficiencyTest` — 各不足コード1件以上 (data provider 全12コード)
* [x] Test: `ValidationDeficiencyTest::testDeficiencyOrderIsStable`
* [x] Test: `ValidationDeficiencyTest::testBuildAndMapUnknownProviderReturnProviderUnsupported`
* [x] Test: `RecordNormalizeTest::testUnknownKeysAreDroppedFromNormalizedRecord`
* [x] Test: `RecordNormalizeTest::testProviderDefaultsToZoomWhenOmitted`
* [x] Test: `ValidationDeficiencyTest::testMultiplePanelistDeficienciesFollowInputOrder`
* [x] Test: `StartAtValidationTest::testStartAtBoundaryMatchesValidationSpec`

### プロバイダ・レジストリ {#status-provider}

正本: [provider_spec.md](./core/provider_spec.md)

* [x] Test: `ValidationDeficiencyTest` — 未知 provider → `provider_unsupported`
* [x] Test: `BuildWebinarRequestTest` / `MapWebinarResponseTest` — build/map 未知 provider
* [x] Test: `TrackingImportTest` — tracking build/map 未知 provider
* [x] Test: `ZoomProvider` — 初版 `zoom` 記述子は登録済み (上記が通る)
* [x] Test: `ProviderRegistryTest::testRegisteringMockProviderDoesNotBreakZoomCreatePath`
* [x] Test: `BuildWebinarRequestTest::testAttachSurveyRequiresSurveyDocumentInStep`
* [x] Test: `BuildFacadeTest::testIdRequiredOpsThrowWhenWebinarIdEmpty`
* [x] Test: `OAuthMaterialsTest` — token / refresh の config 欠落

### 操作計画 {#status-plan}

正本: [operation_spec.md](./core/operation_spec.md)

* [x] Test: `OperationPlanTest::testPlanDoesNotRevalidate`
* [x] Test: `OperationPlanTest::testEmptyPlanWhenNothingToDo`
* [x] Test: `OperationPlanTest::testDeleteOnlyNeverAppendsGet`
* [x] Test: `OperationPlanTest::testCreateWithIntendGetAppendsGetAtEnd`
* [x] Test: `OperationPlanTest::testCreateWithoutSurveyDocumentIsAllowed`
* [x] Test: `OperationPlanTest::testColumnOrderUpdateRemoveAddSurveyGet`
* [x] Test: `OperationPlanTest::testErrorWithIdEmptyPlansCreate`
* [x] Test: `OperationPlanTest::testErrorWithIdNoRetryEmptyOrGetOnly`
* [x] Test: `OperationPlanTest::testRemovePanelistsOneEmailPerOperationElement`
* [x] Test: `OperationPlanTest::testIntendRetryWithoutPanelistDiffMatchesDirtyUpdateColumn`
* [x] Test: `OperationPlanTest::testAttachSurveyStepIncludesSurveyDocument`
* [x] Test: `OperationPlanTest::testIntendGetOnlyReturnsGetWhenSynced`

### リクエスト材料 (CRUD + Panelist) {#status-request}

正本: [request_spec.md](./core/request_spec.md)

* [x] Test: `BuildWebinarRequestTest` — create パス `/users/me/webinars` (Create 成功テスト内)
* [x] Test: `PanelistDiffTest::testAddPanelistsRequestBodyLowercasesEmail`
* [x] Test: `BuildWebinarRequestTest::testAttachSurveyBodyUsesCustomSurveyWrapper`
* [x] Test: `BuildWebinarRequestTest::testUpdateWithEmptyWebinarIdThrows`
* [x] Test: create — `type` = `5` (`MapWebinarResponseTest` 経由の create build)
* [x] Test: `RequestSpecTest::testCreateBodyMatchesRequestSpecSnapshot` — Q&A 設定含む
* [x] Test: `RequestSpecTest::testCreateOmitsAgendaWhenEmptyAndForbiddenKeys`
* [x] Test: `RequestSpecTest::testUpdateIncludesApprovalTypeAndAgendaWhenNonEmpty`
* [x] Test: `RequestSpecTest::testCreateBodyMatchesRequestSpecSnapshot` — `start_time` 壁時計
* [x] Test: `RequestSpecTest::testPathsEmbedWebinarId` / `testCreatePathIsUsersMeWebinars`
* [x] Test: `RequestSpecTest::testRemovePanelistsUsesPerEmailDeletePath`
* [x] Test: `RequestSpecTest::testCreateBodyMatchesRequestSpecSnapshot` — create JSON スナップショット

### Panelist 差分 {#status-panelist}

正本: [panelist_spec.md](./core/panelist_spec.md)

* [x] Test: `PanelistDiffTest::testAddAndRemove`
* [x] Test: `PanelistDiffTest::testNameOnlyChangeIsRemoveThenAdd`
* [x] Test: `PanelistDiffTest::testReorderOnlyProducesNoDiff`
* [x] Test: `ValidationDeficiencyTest` — `panelists_empty` / incomplete / `panelist_email_invalid`
* [x] Test: `PanelistDiffTest::testAddPanelistsRequestBodyLowercasesEmail`
* [x] Test: `PanelistDiffTest::testNameIsTrimmedInDiffAdd`

### 結果写像 {#status-result}

正本: [result_spec.md](./core/result_spec.md)

* [x] Test: `MapWebinarResponseTest::testCreateSuccessSetsIdAndJoinUrl`
* [x] Test: `MapWebinarResponseTest::testCreateFailureKeepsWebinarIdEmpty`
* [x] Test: `MapWebinarResponseTest::testUpdateSuccessOn204NullBody`
* [x] Test: `MapWebinarResponseTest::testPanelistSuccessSetsSynced`
* [x] Test: `MapWebinarResponseTest::testGetSuccessDoesNotChangeStatusReturnsStartUrl`
* [x] Test: `MapWebinarResponseTest::testGetFailureDoesNotChangeStatus`
* [x] Test: `MapWebinarResponseTest::testDeleteFailureKeepsWebinarId` / `testDeleteSuccessClearsIds`
* [x] Test: `MapWebinarResponseTest::testAttachSurveySuccessSynced`
* [x] Test: `MapWebinarResponseTest::testCreateSuccessSetsIdAndJoinUrl` — `webinar_uuid`
* [x] Test: `MapWebinarResponseTest::testGetSuccessDoesNotChangeStatusReturnsStartUrl`
* [x] Test: `MapWebinarResponseTest::testCreateFailureKeepsWebinarIdEmpty` — `last_error`

### OAuth 材料 {#status-OAuth}

正本: [oauth_spec.md](./core/oauth_spec.md)

* [x] Test: `OAuthMaterialsTest::testMissingConfigKeyThrows`
* [x] Test: `OAuthMaterialsTest::testTokenMaterialDoesNotEmbedClientSecret`
* [x] Test: `OAuthMaterialsTest::testRefreshMaterialDoesNotEmbedClientSecret`
* [x] Test: `OAuthMaterialsTest::testAuthorizeUrlDoesNotContainClientSecret`
* [x] Test: `OAuthMaterialsTest` + token — method/パス/body キー (`grant_type`, `code`, `redirect_uri`)
* [x] Test: `OAuthMaterialsTest::testAuthorizeUrlIncludesAllOAuthScopesFromSpec`
* [x] Test: `OAuthMaterialsTest::testRefreshRequestBodyAndFullTokenUrl`
* [x] Test: `OAuthMaterialsTest::testTokenExchangeMissingRedirectUriThrows` / `testRefreshMissingClientSecretThrows`
* [x] Test: `OAuthMaterialsTest::testRefreshRequestBodyAndFullTokenUrl` — token URL

### アンケート写像 {#status-survey}

正本: [survey_map_spec.md](./core/survey_map_spec.md)

* [x] Test: `SurveyMapTest` —5 `answer_kind` → Zoom type
* [x] Test: `SurveyMapTest::testUnsupportedAnswerKindIsOmittedFromQuestions`
* [x] Test: `BuildWebinarRequestTest::testAttachSurveyBodyUsesCustomSurveyWrapper`
* [x] Test: `MapWebinarResponseTest::testAttachSurveySuccessSynced`
* [x] Test: `BuildWebinarRequestTest` — attach_survey step 必須
* [x] Test: `OperationPlanTest` — survey 付き plan
* [x] Test: `SurveyHelperTest::testBuildSurveyUpdateRequestNullTitleMatchesSotWithoutStepTitle` / topic フォールバック
* [x] Test: `SurveyHelperTest::testSurveyTopLevelFlagsAndOmitsInternalNameInQuestions`
* [x] Test: `SurveyHelperTest::testSurveyTopLevelFlagsAndOmitsInternalNameInQuestions` — `internal_name`

### Source Tracking 取り込み {#status-tracking}

正本: [tracking_import_spec.md](./core/tracking_import_spec.md)

* [x] Test: `TrackingImportTest::testBuildEmbedsWebinarIdInPath`
* [x] Test: `TrackingImportTest::testBuildEmptyWebinarIdReturnsTrackingWebinarIdEmpty`
* [x] Test: `TrackingImportTest::testBuildUnknownProviderReturnsProviderUnsupported`
* [x] Test: `TrackingImportTest::testMapArrayJson`
* [x] Test: `TrackingImportTest::testMapWrappedJson`
* [x] Test: `TrackingImportTest::testMapDropsInvalidRowsPartialSuccess`
* [x] Test: `TrackingImportTest::testMapAllInvalidRowsReturnsTrackingSourcesEmpty`
* [x] Test: `TrackingImportTest::testMapNon2xxReturnsTrackingHttpError`
* [x] Test: `TrackingImportTest::testMapInvalidJsonReturnsTrackingBodyInvalid`
* [x] Test: `TrackingImportTest::testMapUnknownProviderReturnsProviderUnsupported`
* [x] Test: `TrackingImportTest::testMapInvalidTopShapeReturnsTrackingBodyInvalid`
* [x] Test: `TrackingImportTest::testMapRejectsSourceNameWithNewline`

### PHPUnit / PHPStan / PHPCS {#status-quality}

正本: [testing.md](./testing.md) / [ci.md](./engineering/ci.md)

* [x] Test: [testing.md](./testing.md) 必須カバレッジ — 不足コード・ plan/build/map 境界 (現行 Unit 群)
* [x] Local: `composer phpstan` がエラーなく通る (level8)
* [x] Local: PHPUnit 全緑 (`composer test`)
* [x] CI: `docs-lint` ([.github/workflows/docs-lint.yml](../.github/workflows/docs-lint.yml))
* [x] CI: [.github/workflows/php-quality.yml](../.github/workflows/php-quality.yml)
* [x] CI: `php-quality` — PHPCS 警告0 (ローカル `composer phpcs`)
* [x] CI (任意): `ContractSnapshotTest` + [tests/Fixtures/contracts/zoom_create_webinar_body.json](../tests/Fixtures/contracts/zoom_create_webinar_body.json)

### Packagist 公開 {#status-Packagist}

正本: [build_and_release.md](./engineering/build_and_release.md)

* [x] CI: [.github/workflows/release.yml](../.github/workflows/release.yml) — タグ `v*.*.*` で品質ゲート + 任意 Packagist API 更新
* [x] Packagist: [s2j/webinar-service](https://packagist.org/packages/s2j/webinar-service) 登録済み (`This package is auto-updated.` / dev-main)
* [x] SemVer: CHANGELOG `0.1.0` 節、初回タグ候補 `v0.1.0` (push は手順書どおり)
* [x] `composer validate-composer` / `composer verify-dist` (.gitattributes)
* [x] README — `composer require` / PHP 8.1+

### プラグインからの require {#status-plugin}

正本: S2J Webinar 側 (本 repo 外)

* [ ] S2J Webinar: `composer require s2j/webinar-service`
* [ ] プラグイン: OAuth + HTTP + `plan` ループ結合
* [ ] プラグイン: tracking-sources REST → `build_tracking_sources_request` / `map_tracking_sources_response`
* [ ] 結合: ステージングで create → panelist → survey 一連

## 補足

* HTTP Adapter (プラグインの HTTP 実行) は、初版仕様の公開面に含めない。記述子上の Adapter 関数とは別物 ([data_dictionary.md](./contracts/data_dictionary.md))。
* Meeting、録画取り込み、効果測定は、初版外。
* **実装％** は、上記チェックリストの `[x]` / 全項の比率 (仕様分割・ Packagist ・プラグインは除く、または表記どおり)。
