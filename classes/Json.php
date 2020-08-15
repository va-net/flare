<?php

/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

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