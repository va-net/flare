<?php

class Time
{

    public static function strToSecs($string)
    {

        sscanf($string, "%d:%d", $hours, $minutes);

        return ($hours * 3600) + ($minutes * 60);

    }

}