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

        $sql = "SELECT pireps.*, pilots.name AS pilotname, aircraft.name AS aircraftname FROM (pireps INNER JOIN pilots ON pireps.pilotid=pilots.id) INNER JOIN aircraft ON pireps.aircraftid=aircraft.id ORDER BY pireps.date DESC";
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

        $result = self::$_db->query("SELECT r.*, p.callsign AS pilotcallsign, p.name AS pilotname FROM `pireps` r INNER JOIN `pilots` p ON p.id=r.pilotid WHERE r.`status`=0");

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

        $usr = new User;
        $pirep = Pirep::find($id);
        if (!$pirep) return;

        $beforeRank = $usr->rank($pirep->pilotid, true);
        self::$_db->update('pireps', $id, 'id', array(
            'status' => 1
        ));
        $afterRank = $usr->rank($pirep->pilotid, true);

        if ($beforeRank != $afterRank) {
            Events::trigger('user/promoted', ["pilot" => $pirep->pilotid, "rank" => $afterRank]);
        }

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

        return self::$_db->getAll('multipliers')->results();
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
     * @return bool|object
     * @param int $code Multiplier Code
     */
    public static function findMultiplier($code)
    {
        self::init();
        $ret = self::$_db->get('multipliers', array('code', '=', $code));
        if ($ret->count() == 0) {
            return false;
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
