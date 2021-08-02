<?php

/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

class Awards implements DataClass
{
    private static $_db = null;

    private static function init()
    {
        if (self::$_db == null) {
            self::$_db = DB::getInstance();
        }
    }

    public static function getAll()
    {
        self::init();
        $q = self::$_db->getAll('awards');
        if ($q->error()) {
            throw new Exception("Failed to fetch awards");
        }

        return $q->results();
    }

    public static function get($id)
    {
        self::init();
        $q = self::$_db->get('awards', ['id', '=', $id]);
        if ($q->error()) {
            throw new Exception("Failed to fetch award");
        }

        if ($q->count() < 1) return null;

        return $q->first();
    }

    public static function create($obj)
    {
        self::init();
        $q = self::$_db->insert('awards', $obj);
        Events::trigger('award/created', $obj);
        return !$q->error();
    }

    public static function update($id, $obj)
    {
        self::init();
        $q = self::$_db->update('awards', $id, 'id', $obj);
        if (!isset($obj['id'])) $obj['id'] = $id;
        Events::trigger('award/updated', $obj);
        return !$q->error();
    }

    public static function delete($id)
    {
        self::init();
        self::$_db->delete('awards_granted', ['awardid', '=', $id]);
        $q = self::$_db->delete('awards', ['id', '=', $id]);
        Events::trigger('award/deleted', ['id' => $id]);
        return !$q->error();
    }

    /**
     * @return array
     * @param int $awardid Award ID
     */
    public static function awardRecipients($awardid)
    {
        self::init();
        $q = self::$_db->query("SELECT * FROM pilots WHERE pilots.id IN (SELECT pilotid FROM awards_granted WHERE awardid=?)", [$awardid]);
        if ($q->error()) {
            throw new Exception("Failed to fetch award receipients");
        }

        return $q->results();
    }

    /**
     * @return array
     * @param int $pilotid Pilot ID
     */
    public static function awardsForPilot($pilotid)
    {
        self::init();
        $q = self::$_db->query("SELECT * FROM awards WHERE awards.id IN (SELECT awardid FROM awards_granted WHERE pilotid=?)", [$pilotid]);
        if ($q->error()) {
            throw new Exception("Failed to fetch pilot awards");
        }

        return $q->results();
    }

    /**
     * @return bool
     * @param int $awardid Award ID
     * @param int $pilotid Pilot ID
     */
    public static function give($awardid, $pilotid)
    {
        self::init();
        $data = [
            'awardid' => $awardid,
            'pilotid' => $pilotid,
            'dateawarded' => date('Y-m-d'),
        ];
        $q = self::$_db->insert('awards_granted', $data);
        Events::trigger('award/given', $data);
        return !$q->error();
    }

    /**
     * @return bool
     * @param int $awardid Award ID
     * @param int $pilotid Pilot ID
     */
    public static function revoke($awardid, $pilotid)
    {
        self::init();
        $q = self::$_db->query("DELETE FROM awards_granted WHERE awardid=? AND pilotid=?", [$awardid, $pilotid]);
        Events::trigger('award/revoked', [
            'awardid' => $awardid,
            'pilotid' => $pilotid,
        ]);
        return !$q->error();
    }
}
