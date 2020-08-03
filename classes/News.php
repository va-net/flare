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

        $result = self::$_db->get('news', array('status', '=', 1), array('dateposted', 'DESC'));

        $x = 0;
        $news = array();

        while ($x < $result->count()) {
            $newdata = array(
                'id' => $result->results()[$x]->id,
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

    public static function archive($id)
    {

        self::init();

        self::$_db->update('news', $id, 'id', array(
            'status' => 2
        ));

    }

    public static function edit($id, $fields) 
    {

        self::init();

        if (self::$_db->update('news', $id, 'id', $fields)) {
            return true;
        }
        return false;

    }

}