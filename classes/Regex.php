<?php

/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

class Regex {

    /**
     * @return bool
     * @param string $pattern RegEx Pattern
     * @param string $string RegEx Subject
     */
    public static function match($pattern, $string)
    {
        return preg_match($pattern, $string) == 1;
    }

}