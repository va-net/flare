<?php

/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

class Analytics
{
    private static $_baseUrl = 'http://localhost:3000';

    /**
     * @param Event $ev
     * @return null
     */
    public static function reportDbError($ev)
    {
        //
    }

    /**
     * @param Exception $ex
     * @return null
     */
    public static function reportException($ex)
    {
        //
    }

    /**
     * @param int $eCode
     * @param string $eMessage
     * @param string $eFile
     * @param int $eLine
     */
    public static function reportError($eCode, $eMessage, $eFile, $eLine)
    {
        //
    }
}
