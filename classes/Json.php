<?php

class Json
{

    public static function decode($data)
    {

        return json_decode($data, true);

    }

    public static function encode($data)
    {

        return json_encode($data);

    }

}