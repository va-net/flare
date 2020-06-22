<?php

class News
{

    private static $_db;

    private static function init()
    {

        self::$_db = DB::getInstance();

    }

    public static function get()
    {

        self::init();

        $result = self::$_db->get('news', array('id', '>', 0), array('dateposted', 'DESC'));

        $x = 0;
        $news = array();

        while ($x < $result->count()) {
            $newdata = array(
                'title' => $result->results()[$x]->subject,
                'author' => $result->results()[$x]->author,
                'content' => $result->results()[$x]->content,
                'dateposted' => $result->results()[$x]->dateposted
            );
            $news[$x] = $newdata;
            $x++;
        }
        return $news;

    }

}