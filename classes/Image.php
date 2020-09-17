<?php

/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

class Image
{

    /**
     * @return string|bool
     * @param array $tmpFile Temp File
     * @param string $where Location
     */
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