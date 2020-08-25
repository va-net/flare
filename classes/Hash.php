<?php

/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

class Hash {

    public static function make($string) {
        return password_hash($string, PASSWORD_DEFAULT);
    }

    public static function check($noHash, $hashed) {
        return password_verify($noHash, $hashed);
    }

    public static function unique() {
        return self::make(uniqid());
    }
}