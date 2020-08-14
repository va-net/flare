<?php

class Time
{

    public static function strToSecs($string)
    {

        sscanf($string, "%d:%d", $hours, $minutes);

        return ($hours * 3600) + ($minutes * 60);

    }

    public static function secsToString($secs)
    {

        $hours = floor($secs / 3600);
        $minutes = floor(($secs / 60) % 60);

        return sprintf("%02d:%02d", $hours, $minutes);

    }

    public static function hrsToSecs($secs)
    {

        return $secs * 3600;

    }

}