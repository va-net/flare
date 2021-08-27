<?php

/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

class Json
{

    /**
     * @return array
     * @param string $data JSON to Decode
     */
    public static function decode($data)
    {

        return json_decode($data, true);

    }

    /**
     * @return string
     * @param mixed $data Data to Encode
     * @param bool $pretty Whether to Pretty Print the JSON
     */
    public static function encode($data, $pretty = false)
    {

        if ($pretty) {
            return json_encode($data, JSON_PRETTY_PRINT);
        }

        return json_encode($data);

    }

}