<?php

/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

class Permissions
{

    private static $_permissions = array(
        'usermanage' => 'User Management',
        'staffmanage' => 'Staff Management',
        'recruitment' => 'Recruitment',
        'pirepmanage' => 'PIREP Management',
        'newsmanage' => 'News Management',
        'opsmanage' => 'Operations Management',
        'statsviewing' => 'View Statistics',
    );
    private static $_db;

    private static function init() 
    {
        self::$_db = DB::getInstance();
    }

    /**
     * @return array
     */
    public static function getAll()
    {
        return self::$_permissions;
    }

    /**
     * @return bool
     * @param int $userid User ID
     * @param string $perm Permission Name
     */
    public static function give($userid, $perm)
    {
        self::init();

        $fields = array(
            'userid' => $userid,
            'name' => $perm
        );
        $ret = self::$_db->insert('permissions', $fields);

        Events::trigger('permission/given', $fields);

        return !($ret->error());
    }

    /**
     * @return bool
     * @param int $userid User ID
     * @param string $perm Permission Name
     */
    public static function revoke($userid, $perm)
    {
        self::init();

        $ret = self::$_db->query("DELETE FROM permissions WHERE userid=? AND name=?", array($userid, $perm));
        Events::trigger('permission/revoked', ["userid" => $userid, "name" => $perm]);

        return !($ret->error());
    }

    /**
     * @return array
     * @param int $userid User ID
     */
    public static function forUser($userid)
    {
        self::init();

        $tempperms = self::$_db->get('permissions', array('userid', '=', $userid))->results();
        $permissions = [];
        foreach ($tempperms as $p) {
            array_push($permissions, $p->name);
        }

        return $permissions;
    }

    /**
     * @return null
     * @param int $userid User ID
     */
    public static function giveAll($userid)
    {
        foreach (self::$_permissions as $key => $val) {
            $ret = self::give($userid, $key);
            if (!$ret) {
                throw new Exception("Could not Give Permission {$val} ({$key})");
            }
        }

        $res = self::give($userid, 'admin');
        if (!res) {
            throw new Exception("Could not Give Permission Admin (admin)");
        }
    }

    /**
     * @return array
     * @param string $perm Permission Key
     */
    public static function usersWith($perm)
    {
        self::init();

        $sql = "SELECT u.* FROM pilots u WHERE u.id IN (SELECT p.userid FROM permissions p WHERE name=?)";
        return self::$_db->query($sql, [$perm])->results();
    }

}