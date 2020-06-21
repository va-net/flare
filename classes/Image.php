<?php

class Image
{

    public static function save($tmpFile, $where) 
    {

        $fileName = $tmpFile['name'];
        $fileTmpName = $tmpFile['tmp_name'];
        $fileSize = $tmpFile['size']; 
        $fileError = $tmpFile['error']; 

        $fileExt = explode('.', $fileName);
        $fileActualExt = strtolower(end($fileExt));

        $allowed = array('jpeg');

        if (in_array($fileActualExt, $allowed)) {
            if ($fileError === 0) {
                if ($fileSize < 1000000) {
                    $uniqueid = uniqid('', true);
                    $fileNameNew = $uniqueid.'.'.$fileActualExt;
                    $fileDest = $where.$fileNameNew;
                    move_uploaded_file($fileTmpName, $fileDest);
                    return $uniqueid;
                } 
            } 
        }
        return false;

    }

}