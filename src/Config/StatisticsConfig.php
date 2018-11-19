<?php

namespace KikCMS\Config;


class StatisticsConfig
{
    const VISITS_DAILY   = 'daily';
    const VISITS_MONTHLY = 'monthly';

    const TYPE_SOURCE     = 'source';
    const TYPE_OS         = 'os';
    const TYPE_PAGE       = 'page';
    const TYPE_BROWSER    = 'browser';
    const TYPE_LOCATION   = 'location';
    const TYPE_RESOLUTION = 'resolution';

    const MAX_IMPORT_ROWS = 10000;

    const GA_TYPES = [
        self::TYPE_SOURCE     => 'ga:source',
        self::TYPE_OS         => 'ga:operatingSystem',
        self::TYPE_PAGE       => 'ga:pagePath',
        self::TYPE_BROWSER    => 'ga:browser',
        self::TYPE_LOCATION   => 'ga:city',
        self::TYPE_RESOLUTION => 'ga:screenResolution',
    ];
}