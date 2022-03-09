<?php

/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

class Rank
{

    /**
     * @var DB
     */
    private static $_db;

    private static function init()
    {

        self::$_db = DB::newInstance();
    }

    /**
     * @return string|int
     * @param int $hours Hours to Get Rank For
     * @param bool $returnid Whether to return the Rank ID
     */
    public static function calc($hours, $returnid = false)
    {

        self::init();

        $ranks = self::$_db->get('ranks', array('id', '>', '0'), array('timereq', 'DESC'))->results();
        foreach ($ranks as $item) {
            if ($item->timereq <= $hours) {
                if ($returnid) {
                    return $item->id;
                } else {
                    return $item->name;
                }
            }
        }
    }

    /**
     * @return int
     * 
     */
    public static function getId($hours)
    {

        self::init();

        return self::calc($hours, true);
    }

    /**
     * @return string
     * @param int $hours Hours to get Rank For
     */
    public static function getName($hours)
    {

        self::init();

        return self::calc($hours, false);
    }

    /**
     * @return void
     * @param string $name Rank Name
     * @param int $timereq Flight Time Required in Seconds
     */
    public static function add($name, $timereq)
    {

        self::init();

        $data = array(
            'name' => $name,
            'timereq' => $timereq
        );
        self::$_db->insert('ranks', $data);

        Events::trigger('rank/added', $data);
    }

    /**
     * @return void
     * @param int $id Rank ID
     * @param array $fields Updated Rank Fields
     */
    public static function update($id, $fields = array())
    {
        self::init();

        if (!self::$_db->update('ranks', $id, 'id', $fields)) {
            throw new Exception('There was a problem updating the user.');
        }

        $fields["id"] = $id;
        Events::trigger('rank/updated', $fields);
    }

    /**
     * @return bool
     * @param int $id Rank ID
     */
    public static function delete($id)
    {
        self::init();

        $ret = self::$_db->delete('ranks', array('id', '=', $id));
        if ($ret->error()) return false;

        $lowRank = self::getFirstRank();
        self::$_db->query("UPDATE aircraft SET rankreq=? WHERE rankreq=?", [$lowRank->id, $id]);

        Events::trigger('rank/deleted', ['id' => $id]);
        return true;
    }

    /**
     * @return object
     */
    public static function getFirstRank()
    {

        self::init();
        $rank = self::$_db->get('ranks', array('id', '>', '0'), array('timereq', 'asc'));
        return $rank->first();
    }

    /**
     * @return string
     * @param int $id Rank ID
     */
    public static function idToName($id)
    {

        self::init();
        $rank = self::$_db->get('ranks', array('id', '=', $id));
        return $rank->first()->name;
    }

    /**
     * @return object|bool
     * @param int $id Rank ID
     */
    public static function find($id)
    {
        self::init();

        $ret = self::$_db->get('ranks', ['id', '=', $id]);
        if ($ret->count() == 0) return false;

        return $ret->first();
    }

    /**
     * @return int
     * @param string $name Rank Name
     */
    public static function nameToId($name)
    {

        self::init();
        $rank = self::$_db->get('ranks', array('name', '=', $name));
        return $rank->first()->id;
    }

    /**
     * @return DB
     */
    public static function fetchAllNames()
    {

        self::init();
        return self::$_db->get('ranks', ['1', '=', '1'], ['timereq', 'ASC']);
    }
}
