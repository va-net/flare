<?php

class Redirect {

    public static function to($location = null) {
        if ($location) {
            if (is_numeric($location)) {
                switch ($location) {
                    case 404:
                        http_response_code(404);
                        break;
                }
            }
            header('Location: '.$location);
            exit();
        }
    }

}