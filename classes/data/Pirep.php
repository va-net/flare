<?php

/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

class Pirep
{

    /**
     * @var DB
     */
    private static $_db;

    private static function init()
    {
        self::$_db = DB::getInstance();
    }

    /**
     * @return bool
     * @param array $fields PIREP Fields
     */
    public static function file($fields = array())
    {
        self::init();
        if (self::$_db->insert('pireps', $fields)->error()) {
            return false;
        }
        Events::trigger('pirep/filed', $fields);
        return true;
    }

    /**
     * @return bool
     * @param int $id PIREP ID
     * @param array $fields Updated PIREP Fields
     */
    public static function update($id, $fields = array())
    {
        self::init();

        if (self::$_db->update('pireps', $id, 'id', $fields)->error()) {
            return false;
        }
        $fields["id"] = $id;
        Events::trigger('pirep/updated', $fields);
        return true;
    }

    /**
     * @return bool
     * @param int $id PIREP ID
     */
    public static function delete($id)
    {
        self::init();

        $res = !(self::$_db->delete('pireps', ['id', '=', $id])->error());
        if ($res) {
            Events::trigger('pirep/deleted', ['id' => $id]);
        }

        return $res;
    }

    /**
     * @return array
     */
    public static function fetchAll()
    {
        self::init();

        $sql = "SELECT pireps.*, pilots.name AS pilotname, pilots.callsign AS pilotcallsign, aircraft.name AS aircraftname FROM (pireps INNER JOIN pilots ON pireps.pilotid=pilots.id) INNER JOIN aircraft ON pireps.aircraftid=aircraft.id ORDER BY pireps.date DESC";
        $result = self::$_db->query($sql)->results();
        $pireps = array_map(function ($p) {
            return (array)$p;
        }, $result);
        return $pireps;
    }

    /**
     * @return array
     * @param int $days
     */
    public static function fetchPast($days)
    {
        self::init();

        $sql = "SELECT pireps.*, pilots.name AS pilotname, aircraft.name AS aircraftname FROM (pireps INNER JOIN pilots ON pireps.pilotid=pilots.id) INNER JOIN aircraft ON pireps.aircraftid=aircraft.id WHERE DATEDIFF(NOW(), pireps.date) <= ? ORDER BY pireps.date ASC";
        $result = self::$_db->query($sql, [$days], true)->results();
        $pireps = array_map(function ($p) {
            return (array)$p;
        }, $result);
        return $pireps;
    }

    /**
     * @return array
     */
    public static function fetchPending()
    {

        self::init();

        $sql = "SELECT pireps.*, pilots.name AS pilotname, pilots.callsign AS pilotcallsign, aircraft.name AS aircraftname FROM (pireps INNER JOIN pilots ON pireps.pilotid=pilots.id) INNER JOIN aircraft ON pireps.aircraftid=aircraft.id WHERE pireps.status=0 ORDER BY pireps.date ASC";
        $result = self::$_db->query($sql);

        $pireps = array_map(function ($x) {
            return (array)$x;
        }, $result->results());
        return $pireps;
    }

    /**
     * @return int
     */
    public static function pendingCount()
    {
        self::init();
        return self::$_db->query("SELECT COUNT(id) AS result FROM `pireps` WHERE `status`=0")->first()->result;
    }

    /**
     * @return void
     * @param int $id PIREP ID
     */
    public static function accept($id)
    {
        self::init();

        $pirep = Pirep::find($id);
        if (!$pirep) return;

        self::$_db->update('pireps', $id, 'id', array(
            'status' => 1
        ));

        $pirep->status = 1;
        Events::trigger('pirep/accepted', (array)$pirep);
    }

    /**
     * @return void
     * @param int $id PIREP ID
     */
    public static function decline($id)
    {
        self::init();

        self::$_db->update('pireps', $id, 'id', array(
            'status' => 2
        ));
        Events::trigger('pirep/denied', ["id" => $id]);
    }

    /**
     * @return array
     */
    public static function fetchMultipliers()
    {
        self::init();

        $sql = "SELECT m.*, r.name AS minrank FROM multipliers m LEFT JOIN ranks r ON m.minrankid=r.id ORDER BY m.multiplier ASC";
        return self::$_db->query($sql)->results();
    }

    /**
     * @return int
     * @param array $multis Array of Multipliers
     */
    public static function generateMultiCode($multis = null)
    {
        if ($multis == null) {
            $multis = self::fetchMultipliers();
        }
        $codes = array();
        foreach ($multis as $m) {
            array_push($codes, $m->code);
        }

        $code = mt_rand(111111, 999999);
        while (in_array($code, $codes)) {
            $code = mt_rand(111111, 999999);
        }

        return $code;
    }

    /**
     * @return void
     * @param int $id Multiplier ID
     */
    public static function deleteMultiplier($id)
    {
        self::init();

        self::$_db->delete('multipliers', array('id', '=', $id));
        Events::trigger('pirep/multipliers/deleted', ["id" => $id]);
    }

    /**
     * @return void
     * @param array $fields Multiplier Fields
     */
    public static function addMultiplier($fields = array())
    {
        self::init();

        self::$_db->insert('multipliers', $fields);
        Events::trigger('pirep/multipliers/added', $fields);
    }

    /**
     * @return object|null
     * @param int $code Multiplier Code
     * @param int|null $rankid User Rank ID
     */
    public static function findMultiplier($code, $rankid)
    {
        self::init();
        if ($rankid == null) {
            $ret = self::$_db->get('multipliers', array('code', '=', $code));
            if ($ret->count() == 0) {
                return null;
            }
            return $ret->first();
        }

        $sql = "SELECT m.* FROM multipliers m WHERE m.code=? AND (m.minrankid IS NULL OR (SELECT timereq FROM ranks WHERE id=?) >= (SELECT timereq FROM ranks WHERE id=m.minrankid)) LIMIT 1";
        $ret = self::$_db->query($sql, [$code, $rankid]);

        if ($ret->count() == 0) {
            return null;
        }

        return $ret->first();
    }

    /**
     * @return object|null
     * @param int $id Multiplier ID
     */
    public static function findMultiplierById($id)
    {
        self::init();
        $ret = self::$_db->get('multipliers', array('id', '=', $id));
        if ($ret->count() == 0) {
            return null;
        }

        return $ret->first();
    }

    /**
     * @return object|null
     * @param string $name Multiplier Name
     */
    public static function findMultiplierByName($name)
    {
        self::init();
        $ret = self::$_db->get('multipliers', array('name', '=', $name));
        if ($ret->count() == 0) {
            return null;
        }

        return $ret->first();
    }

    /**
     * @return object|bool
     * @param int $id PIREP ID
     * @param int $pilot Pilot ID
     */
    public static function find($id, $pilot = null)
    {
        self::init();

        $pirep = self::$_db->get('pireps', ['id', '=', $id]);
        if ($pirep->count() == 0) {
            return false;
        }

        if ($pilot == null) {
            return $pirep->first();
        }

        if ($pirep->first()->pilotid == $pilot) {
            return $pirep->first();
        }

        return false;
    }

    /**
     * @return array|null
     * @param int $pirepid PIREP ID
     */
    public static function getComments($pirepid)
    {
        self::init();
        $q = self::$_db->query(
            "SELECT c.id, c.content, c.dateposted, p.name AS userName FROM pireps_comments c INNER JOIN pilots p ON c.userid=p.id WHERE c.pirepid=? ORDER BY c.dateposted",
            [$pirepid]
        );
        if ($q->error()) return null;

        return $q->results();
    }


    /**
     * @return array|null
     * @param int $pirepid PIREP ID
     */
    public static function getAllComments()
    {
        self::init();
        $q = self::$_db->getAll('pireps_comments');
        if ($q->error()) return null;

        return $q->results();
    }

    /**
     * @return array|null
     * @param int $id Comment ID
     */
    public static function findComment($id)
    {
        self::init();
        $q = self::$_db->query(
            "SELECT c.id, c.content, c.dateposted, p.name AS userName FROM pireps_comments c INNER JOIN pilots p ON c.userid=p.id WHERE c.id=?",
            [$id]
        );
        if ($q->error()) throw new Exception("Failed to fetch PIREP comment");
        if ($q->count() < 1) return null;

        return $q->first();
    }

    /**
     * @return bool
     * @param array $obj Comment Data
     */
    public static function addComment($obj)
    {
        self::init();

        $q = self::$_db->insert('pireps_comments', $obj);

        if (!$q->error()) Events::trigger('pirep/comment_added', $obj);

        return !$q->error();
    }

    /**
     * @return bool
     * @param int $id Comment ID
     * @param array $obj Updated Comment Data
     */
    public static function updateComment($id, $obj)
    {
        self::init();

        $q = self::$_db->update('pireps_comments', $id, 'id', $obj);

        if (!isset($obj['id'])) $obj['id'] = $id;
        if (!$q->error()) Events::trigger('pirep/comment_updated', $obj);

        return !$q->error();
    }

    /**
     * @return bool
     * @param int $id Comment ID
     */
    public static function deleteComment($id)
    {
        self::init();

        $q = self::$_db->delete('pireps_comments', ['id', '=', $id]);
        if (!$q->error()) Events::trigger('pirep/comment_deleted', ['id' => $id]);

        return !$q->error();
    }

    public static function getByAirport($icao)
    {
        self::init();

        $sql = "SELECT pireps.*, pilots.name AS pilotname FROM pireps INNER JOIN pilots ON pireps.pilotid=pilots.id WHERE pireps.departure=? OR pireps.arrival=? ORDER BY pireps.date DESC";
        $q = self::$_db->query($sql, [$icao, $icao]);
        if ($q->error()) return null;

        return $q->results();
    }
}
