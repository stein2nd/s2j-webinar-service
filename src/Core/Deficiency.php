<?php

declare(strict_types=1);

namespace S2J\WebinarService\Core;

final class Deficiency
{
    public const TOPIC_EMPTY = 'topic_empty';
    public const TOPIC_TOO_LONG = 'topic_too_long';
    public const AGENDA_TOO_LONG = 'agenda_too_long';
    public const START_AT_INVALID = 'start_at_invalid';
    public const TIMEZONE_INVALID = 'timezone_invalid';
    public const DURATION_INVALID = 'duration_invalid';
    public const AUTO_RECORDING_INVALID = 'auto_recording_invalid';
    public const APPROVAL_TYPE_INVALID = 'approval_type_invalid';
    public const PANELISTS_EMPTY = 'panelists_empty';
    public const PANELIST_INCOMPLETE = 'panelist_incomplete';
    public const PANELIST_EMAIL_INVALID = 'panelist_email_invalid';
    public const PROVIDER_UNSUPPORTED = 'provider_unsupported';

    public const TRACKING_WEBINAR_ID_EMPTY = 'tracking_webinar_id_empty';
    public const TRACKING_HTTP_ERROR = 'tracking_http_error';
    public const TRACKING_BODY_INVALID = 'tracking_body_invalid';
    public const TRACKING_SOURCES_EMPTY = 'tracking_sources_empty';

    /** @var list<string> */
    public const WEBINAR_RECORD_ORDER = [
        self::TOPIC_EMPTY,
        self::TOPIC_TOO_LONG,
        self::AGENDA_TOO_LONG,
        self::START_AT_INVALID,
        self::TIMEZONE_INVALID,
        self::DURATION_INVALID,
        self::AUTO_RECORDING_INVALID,
        self::APPROVAL_TYPE_INVALID,
        self::PANELISTS_EMPTY,
        self::PANELIST_INCOMPLETE,
        self::PANELIST_EMAIL_INVALID,
        self::PROVIDER_UNSUPPORTED,
    ];

    /** @var list<string> */
    public const WEBINAR_OPS = [
        'create',
        'update',
        'delete',
        'get',
        'add_panelists',
        'remove_panelists',
        'attach_survey',
    ];

    /** @var list<string> */
    public const WEBINAR_ID_REQUIRED_OPS = [
        'update',
        'delete',
        'get',
        'add_panelists',
        'remove_panelists',
        'attach_survey',
    ];
}
