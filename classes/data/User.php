<?php

/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

class User
{

    private $_db,
        $_data,
        $_sessionName,
        $_cookieName,
        $_isLoggedIn,
        $_permissions;


    public function __construct($user = null)
    {
        $this->_db = DB::getInstance();

        $this->_sessionName = 'user';
        $this->_cookieName = 'remember';

        if (!$user) {
            if (Session::exists($this->_sessionName)) {
                $user = Session::get($this->_sessionName);

                if ($this->find($user)) {
                    $this->_isLoggedIn = true;
                } else {
                    $this->logout();
                }
            }
        } else {
            $this->find($user);
        }
    }

    /**
     * @return void
     * @param array $fields User Fields
     */
    public function create($fields)
    {
        if (!$this->_db->insert('pilots', $fields)) {
            throw new Exception('There was a problem creating an account.');
        }

        Events::trigger('user/created', $fields);
    }

    /**
     * @return boid
     * @param int $id User ID
     */
    public function delete($id)
    {
        $this->_db->delete('pireps', ['pilotid', '=', $id]);
        Permissions::revokeAll($id);
        $this->_db->delete('notifications', ['pilotid', '=', $id]);
        $this->_db->delete('pilots', ['id', '=', $id]);
    }

    /**
     * @return bool
     * @param int|string $user Email or User ID
     */
    public function find($user = null)
    {
        if ($user) {
            $field = (is_numeric($user)) ? 'id' : 'email';
            $data = $this->_db->query("SELECT * FROM pilots WHERE {$field}=? AND status=1", [$user]);
            if ($data->count()) {
                $this->_data = $data->first();
                $this->_permissions = [];
                $tempperms = $this->_db->get('permissions', array('userid', '=', $data->first()->id))->results();
                foreach ($tempperms as $p) {
                    array_push($this->_permissions, $p->name);
                }
                return true;
            }
        }
        return false;
    }

    /**
     * @return bool
     * @param string $username
     * @param string $password
     * @param bool $remember
     */
    public function login($username = null, $password = null, $remember = false)
    {
        $user = $this->find($username);

        if (!$username && !$password && $this->exists()) {
            Session::create($this->_sessionName, $this->data()->id);
        } else {
            $user = $this->find($username);

            if ($user) {
                if (Hash::check($password, $this->data()->password)) {
                    Session::create($this->_sessionName, $this->data()->id);
                    Events::trigger('user/logged-in', (array)$this->data());
                    return true;
                }
            }
        }
        $_SESSION = array();
        Events::trigger('user/login-failed');
        return false;
    }

    /**
     * @return bool
     * @param string $vanetId VANet User ID
     */
    public function vanetLogin($vanetId)
    {
        $user = self::fetchByVanetId($vanetId);
        if (empty($user)) {
            return false;
        }

        $this->find($user->id);
        Session::create($this->_sessionName, $user->id);
        Events::trigger('user/logged-in', (array)$this->data());
        return true;
    }

    /**
     * @return bool
     */
    public function exists()
    {
        return (!empty($this->_data)) ? true : false;
    }

    /**
     * @return object
     */
    public function data()
    {
        return $this->_data;
    }

    /**
     * @return bool
     */
    public function isLoggedIn()
    {
        return $this->_isLoggedIn;
    }

    /**
     * @return void
     */
    public function logout()
    {
        Session::delete($this->_sessionName);
        Cookie::delete($this->_cookieName);
        Events::trigger('user/logged-out', (array)$this->data());
    }

    /**
     * @return void
     * @param array $fields Updated User Fields
     * @param int $id User ID
     */
    public function update($fields = array(), $id = null)
    {
        if (!$id && $this->isLoggedIn()) {
            $id = $this->data()->id;
        }

        if (!$this->_db->update('pilots', $id, 'id', $fields)) {
            throw new Exception('There was a problem updating the user.');
        }

        foreach ($fields as $k => $v) {
            $this->_data->$k = $v;
        }

        $fields["id"] = $id;
        Events::trigger('user/updated', $fields);
    }

    /**
     * @return bool
     * @param string $key Permission Name
     * @param int $id User ID
     */
    public function hasPermission($key, $id = null)
    {
        if (!$id && $this->isLoggedIn()) {
            $id = $this->data()->id;
        }

        $permissions = [];
        if ($id == $this->data()->id) {
            $permissions = $this->_permissions;
        } else {
            $tempperms = $this->_db->get('permissions', array('userid', '=', $id))->results();
            foreach ($tempperms as $p) {
                array_push($permissions, $p->name);
            }
        }

        if (in_array($key, $permissions)) {
            return true;
        }
        return false;
    }

    /**
     * @return int|string
     * @param int $id User ID
     * @param bool $returnid Whether to Return the Rank ID
     */
    public function rank($id = null, $returnid = false)
    {
        if (!$id && $this->isLoggedIn()) {
            $id = $this->data()->id;
        }

        $time = $this->getFlightTime($id);

        if ($returnid) {
            return Rank::getId($time);
        }

        return Rank::calc($time);
    }

    /**
     * @return object
     * @param int $id User ID
     */
    public function nextrank($id = null)
    {
        if (!$id && $this->isLoggedIn()) {
            $id = $this->data()->id;
        }

        $current = $this->rank($id, true);
        $qry = $this->_db->query("SELECT * FROM `ranks` WHERE `timereq` > (SELECT `timereq` FROM `ranks` WHERE id=?) ORDER BY `timereq` ASC LIMIT 1", [$current]);

        if ($qry->count()) {
            return $qry->first();
        }

        return null;
    }

    /**
     * @return array
     * @param int $id User ID
     */
    public function getAvailableAircraft($id = null)
    {
        if (!$id && $this->isLoggedIn()) {
            $id = $this->data()->id;
        }

        $ftime = $this->getFlightTime($id);
        $sql = "SELECT a.* FROM aircraft a WHERE (a.rankreq IN (SELECT r.id FROM ranks r WHERE r.timereq <= ?) OR a.awardreq IN (SELECT w.id FROM awards w WHERE w.id IN (SELECT g.awardid FROM awards_granted g WHERE pilotid=?))) AND a.status=1 ORDER BY a.name ASC, a.liveryname ASC";
        $data = $this->_db->query($sql, [$ftime, $id])->results();

        $aircraft = array_map(function ($a) {
            return (array)$a;
        }, $data);

        return $aircraft;
    }

    /**
     * @return int
     * @param int $id User ID
     */
    public function getFlightTime($id = null)
    {
        if (!$id && $this->isLoggedIn()) {
            $id = $this->data()->id;
        }

        $time = 0;
        if ($id == $this->data()->id) {
            $time = $this->data()->transhours;
        } else {
            $result = $this->_db->get('pilots', array('id', '=', $id));
            $time = $result->first()->transhours;
        }

        $pireps = $this->fetchApprovedPireps();

        if ($pireps->count() != 0) {
            foreach (range(0, $pireps->count() - 1) as $i) {
                $time += $pireps->results()[$i]->flighttime;
            }
        }

        return $time;
    }

    /**
     * @return int
     * @param int $id User ID
     */
    public function numPirepsFiled($id = null)
    {
        if (!$id && $this->isLoggedIn()) {
            $id = $this->data()->id;
        }

        $result = $this->_db->query('SELECT id FROM pireps WHERE status = 1 AND pilotid = ?', array($id));
        $filed = $result->count();

        $trans = 0;
        if ($id == $this->data()->id) {
            $trans = $this->data()->transflights;
        } else {
            $user = $this->_db->get('pilots', array('id', '=', $id));
            $trans = $result->first()->transflights;
        }

        $total = $trans + $filed;
        return $total;
    }

    /**
     * @return DB
     * @param int $id User ID
     * @param int $limit Row Limit
     */
    public function fetchPireps($id = null, $limit = null)
    {
        if ($id == null) {
            $id = $this->data()->id;
        }

        if (!is_numeric($limit) && $limit != null) {
            throw new Exception("Limit Parameter is NaN");
        }

        $sql = "SELECT pireps.*, aircraft.name AS aircraft FROM pireps INNER JOIN aircraft ON pireps.aircraftid=aircraft.id WHERE pilotid = ? ORDER BY date DESC";
        if ($limit != null) {
            $sql .= " LIMIT {$limit}";
        }

        return $this->_db->query($sql, array($id));
    }

    /**
     * @return array
     * @param int $id User ID
     * @param int $num Number of PIREPs to Return
     */
    public function recentPireps($id = null, $num = 10)
    {
        if (!$id && $this->isLoggedIn()) {
            $id = $this->data()->id;
        }

        $results = $this->fetchPireps($id, $num)->results();

        if (count($results) < 1) {
            return [];
        }

        $pireps = array_map(function ($pirep) {
            $statuses = array('Pending', 'Approved', 'Denied');
            return array(
                'id' => $pirep->id,
                'fnum' => $pirep->flightnum,
                'departure' => $pirep->departure,
                'arrival' => $pirep->arrival,
                'date' => date_format(date_create($pirep->date), 'Y-m-d'),
                'status' => $statuses[$pirep->status],
                'flighttime' => $pirep->flighttime,
                'multi' => $pirep->multi,
                'aircraft' => $pirep->aircraft,
            );
        }, $results);
        return $pireps;
    }

    /**
     * @return DB
     * @param int $id User ID
     */
    public function fetchApprovedPireps($id = null)
    {
        if (!$id && $this->isLoggedIn()) {
            $id = $this->data()->id;
        }

        return $this->_db->query('SELECT * FROM pireps WHERE pilotid = ? AND status = ?', array($id, 1));
    }

    /**
     * @return int
     * @param int $id User ID
     */
    public function totalPirepsFiled($id = null)
    {
        if (!$id && $this->isLoggedIn()) {
            $id = $this->data()->id;
        }

        $result = $this->_db->get('pireps', array('status', '=', 1));
        $count = $result->count();
        $user = $this->_db->get('pilots', array('id', '=', $id));
        $total = $user->first()->transflights;

        $x = 0;

        while ($x < $count) {
            $total++;
            $x++;
        }
        return $total;
    }

    /**
     * @return array
     */
    public function getAllUsers()
    {
        $db = DB::newInstance();

        $sql = "SELECT u.*, (SELECT SUM(flighttime) FROM pireps p WHERE p.pilotid=u.id AND `status`=1) AS flighttime FROM pilots u";
        $results = $db->query($sql)->results();

        $usersarray = array();
        $statuses = array('Pending', 'Active', 'Inactive', 'Declined');
        $x = 0;
        $admins = Permissions::usersWith('admin');
        $admins = array_map(function ($item) {
            return $item->id;
        }, $admins);
        foreach ($results as $r) {
            $newdata = array(
                'id' => $r->id,
                'callsign' => $r->callsign,
                'name' => $r->name,
                'email' => $r->email,
                'ifc' => $r->ifc,
                'status' => $statuses[$r->status],
                'joined' => $r->joined,
                'transhours' => $r->transhours,
                'transflights' => $r->transflights,
                'isAdmin' => in_array($r->id, $admins) ? 1 : 0,
                'flighttime' => $r->flighttime == null ? 0 : $r->flighttime,
                'ifuserid' => $r->ifuserid,
                'notes' => $r->notes,
            );
            $usersarray[$x] = $newdata;
            $x++;
        }

        return $usersarray;
    }

    /**
     * @return array
     */
    public static function getActiveUsers()
    {
        $db = DB::newInstance();
        return $db->get('pilots', ['status', '=', 1])->results();
    }

    /**
     * @return array
     */
    public function getAllStaff()
    {
        $sql = "SELECT u.* FROM pilots u WHERE u.id IN (SELECT p.userid FROM permissions p WHERE p.name='admin') AND u.status=1";
        $res = $this->_db->query($sql)->results();
        $ret = [];
        foreach ($res as $staff) {
            array_push($ret, (array)$staff);
        }

        return $ret;
    }

    /**
     * @return array
     */
    public function getAllPendingUsers()
    {
        $db = DB::newInstance();
        $results = $db->get('pilots', array('status', '=', 0));

        return array_map(function ($user) {
            $statuses = ['Pending', 'Active', 'Inactive'];
            return [
                'id' => $user->id,
                'callsign' => $user->callsign,
                'name' => $user->name,
                'email' => $user->email,
                'ifc' => $user->ifc,
                'status' => $statuses[$user->status],
                'joined' => $user->joined,
                'grade' => $user->grade,
                'viol' => $user->violand,
                'notes' => $user->notes,
                'joined' => $user->joined,
            ];
        }, $results->results());
    }

    /**
     * @return string
     * @param int $id User ID
     */
    public function idToCallsign($id)
    {
        $result = $this->_db->get('pilots', array('id', '=', $id));
        $result =  $result->first();
        return $result->callsign;
    }

    /**
     * @return object
     * @param int $id User ID
     */
    public function getUser($id)
    {
        $ret = $this->_db->get('pilots', array('id', '=', $id))->first();

        return $ret;
    }

    /**
     * @return array
     * @param int|null $id Pilot ID
     */
    public function getAwards($id = null)
    {
        if (!$id && $this->isLoggedIn()) {
            $id = $this->data()->id;
        }

        return Awards::awardsForPilot($id);
    }

    /**
     * @return int
     */
    public static function pendingCount()
    {
        $db = DB::getInstance();
        return $db->query("SELECT COUNT(id) AS result FROM `pilots` WHERE `status`=0")->first()->result;
    }

    /**
     * @return array
     * @param int $days
     */
    public static function fetchPast($days)
    {
        $db = DB::getInstance();

        $sql = "SELECT u.*, (SELECT SUM(flighttime) FROM pireps p WHERE p.pilotid=u.id AND status=1) AS flighttime FROM pilots u WHERE DATEDIFF(NOW(), u.joined) <= ? ORDER BY u.joined ASC";
        $results = $db->query($sql, [$days], true)->results();

        $usersarray = [];
        $statuses = array('Pending', 'Active', 'Inactive', 'Declined');
        $admins = Permissions::usersWith('admin');
        $admins = array_map(function ($item) {
            return $item->id;
        }, $admins);
        foreach ($results as $r) {
            $newdata = array(
                'id' => $r->id,
                'callsign' => $r->callsign,
                'name' => $r->name,
                'email' => $r->email,
                'ifc' => $r->ifc,
                'status' => $statuses[$r->status],
                'joined' => $r->joined,
                'transhours' => $r->transhours,
                'transflights' => $r->transflights,
                'isAdmin' => in_array($r->id, $admins) ? 1 : 0,
                'flighttime' => $r->flighttime == null ? 0 : $r->flighttime,
            );
            $usersarray[] = $newdata;
        }

        return $usersarray;
    }

    /**
     * @return object|null
     * @param int $id User VANet ID
     */
    public static function fetchByVanetId($id)
    {
        $db = DB::getInstance();

        $sql = "SELECT * FROM pilots WHERE vanet_id=? AND `status`=1";
        $results = $db->query($sql, [$id]);
        if ($results->count() == 0) {
            return null;
        }

        return $results->first();
    }

    /**
     * @return int
     */
    public static function nextId()
    {
        $db = DB::getInstance();

        $data = $db->query("SHOW TABLE STATUS")->results();
        $table = array_values(array_filter($data, function ($x) {
            return $x->Name == 'aircraft';
        }))[0];

        return $table->Auto_increment;
    }
}
