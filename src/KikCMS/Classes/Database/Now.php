<?php

namespace KikCMS\Classes\Database;


use DateTime;
use KikCMS\Config\KikCMSConfig;

/**
 * Represents the current dateTime.
 * On insert it will convert to a format SQL uses
 */
class Now
{
    /** @var DateTime */
    private $dateTime;

    public function __construct()
    {
        $this->dateTime = new DateTime();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return "'" . $this->dateTime->format(KikCMSConfig::SQL_DATE_FORMAT) . "'";
    }
}