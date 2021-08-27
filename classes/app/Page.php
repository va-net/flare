<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

class Page
{

    private static $_title = '';
    public static $badges = [
        "codeshares" => "VANet::getCodeshareCount",
        "recruitment" => "User::pendingCount",
        "pireps" => "Pirep::pendingCount",
        "settings" => "Updater::updateAvailable",
    ];
    /**
     * @var stdClass
     */
    public static $pageData;

    /**
     * @return void
     * @param string $title Title
     */
    public static function setTitle($title)
    {
        self::$_title = $title;
    }

    /**
     * @return string
     */
    public static function getTitle()
    {
        return self::$_title;
    }
}
