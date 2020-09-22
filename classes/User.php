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

        $this->_sessionName = Config::get('session/session_name');
        $this->_cookieName = Config::get('remember/cookie_name');

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
     * @return null
     * @param array $fields User Fields
     */
    public function create($fields) 
    {

        if (!$this->_db->insert('pilots', $fields)) {
            throw new Exception('There was a problem creating an account.');
        }

    }

    /**
     * @return bool
     * @param int|string $user Email or User ID
     */
    public function find($user = null) 
    {

        if ($user) {
            $field = (is_numeric($user)) ? 'id' : 'email';
            $data = $this->_db->get('pilots', array($field, '=', $user));
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
    public function login($username = null, $password = null, $remember = false) {
        $user = $this->find($username);     
        
        if (!$username && !$password && $this->exists()) {
            Session::create($this->_sessionName, $this->data()->id);
        } else {
            $user = $this->find($username);    

            if ($user) {
                if(Hash::check($password, $this->data()->password)) {
                    Session::create($this->_sessionName, $this->data()->id);

                    if ($remember) {
                        $hash = Hash::unique();
                        $hashCheck = $this->_db->get('sessions', array('user_id', '=', $this->data()->id));

                        if (!$hashCheck->count()) {
                            $this->_db->insert('sessions', array(
                                'user_id' => $this->data()->id,
                                'hash' => $hash
                            ));
                        } else {
                            $hash = $hashCheck->first()->hash;
                        }

                        Cookie::create($this->_cookieName, $hash, Config::get('remember/cookie_expiry'));

                    }

                    return true;
                }
            }
        }
        $_SESSION = array();
        return false;

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
     * @return null
     */
    public function logout() 
    {

        Session::delete($this->_sessionName);
        Cookie::delete($this->_cookieName);

    }

    /**
     * @return null
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
     * @return array
     * @param int $id User ID
     */
    public function getAvailableAircraft($id = null)
    {

        if (!$id && $this->isLoggedIn()) {
            $id = $this->data()->id;
        }

        $result = Aircraft::getAvailableAircraft($this->rank($id, true));

        $aircraft = array();
        $x = 0;

        while ($x < $result->count()) {
            $newdata = array(
                'name' => $result->results()[$x]->name,
                'liveryname' => $result->results()[$x]->liveryname,
                'id' => $result->results()[$x]->id,
            );
            $aircraft[$x] = $newdata;
            $x++;
        }
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
     * @return int
     * @param int $id User ID
     */
    public function fetchPireps($id = null)
    {

        if ($id == null) {
            $id = $this->data()->id;
        }

        return $this->_db->query('SELECT pireps.*, aircraft.name AS aircraft FROM pireps INNER JOIN aircraft ON pireps.aircraftid=aircraft.id WHERE pilotid = ? ORDER BY date DESC', array($id));
        //return $this->_db->get('pireps', array('pilotid', '=', $id), array('date', 'DESC'));

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

        $results = $this->fetchPireps($id);

        $x = 0;
        $counter = 0;
        $pireps = array();
        $statuses = array('Pending', 'Approved', 'Denied');

        if ($results->count() < 1) {
            return false;
        }

        $myPireps = $results->results();
        foreach ($myPireps as $pirep) {
            $newdata = array(
                'id' => $pirep->id,
                'number' => $pirep->flightnum,
                'departure' => $pirep->departure,
                'arrival' => $pirep->arrival,
                'date' => $pirep->date,
                'status' => $statuses[$pirep->status],
                'flighttime' => $pirep->flighttime,
                'multi' => $pirep->multi,
                'aircraft' => $pirep->aircraft,
            );
            $pireps[$x] = $newdata;
            $counter++;
            if ($counter >= $num) {
                break;
            }
            $x++;
        }
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

        $results = $db->getAll('pilots');

        $usersarray = array();
        $statuses = array('Pending', 'Active', 'Inactive');
        $x = 0;

        while ($x < $results->count()) {
            $newdata = array(
                'id' => $results->results()[$x]->id,
                'callsign' => $results->results()[$x]->callsign,
                'name' => $results->results()[$x]->name,
                'email' => $results->results()[$x]->email,
                'ifc' => $results->results()[$x]->ifc,
                'rank' => $this->rank($results->results()[$x]->id),
                'status' => $statuses[$results->results()[$x]->status],
                'joined' => $results->results()[$x]->joined,
                'permissions' => $results->results()[$x]->permissions,
                'transhours' => $results->results()[$x]->transhours,
                'transflights' => $results->results()[$x]->transflights,
                'isAdmin' => Json::decode($results->results()[$x]->permissions)["admin"],
            );
            $usersarray[$x] = $newdata;
            $x++;
        }

        return $usersarray;

    }

    /**
     * @return array
     */
    public function getAllStaff()
    {

        $users = $this->getAllUsers();
        $staff = array();
        $x = 0;

        foreach ($users as $user) {
            $permissions = Json::decode($user['permissions']);
            
            if (array_key_exists('admin', $permissions)) {
                if ($permissions['admin'] == 1) {
                    $newdata = array(
                        'id' => $user['id'],
                        'callsign' => $user['callsign'],
                        'name' => $user['name'],
                        'email' => $user['email'],
                        'ifc' => $user['ifc'],
                        'rank' => $user['rank'],
                        'status' => $user['status'],
                        'joined' => $user['joined'],
                        'permissions' => $permissions
                    );
                    $staff[$x] = $newdata;
                }
            }
            $x++;
        }
        return $staff;

    }

    /**
     * @return array
     */
    public function getAllPendingUsers()
    {

        $db = DB::newInstance();

        $results = $db->get('pilots', array('status', '=', 0));

        $usersarray = array();
        $statuses = array('Pending', 'Active', 'Inactive');
        $x = 0;

        while ($x < $results->count()) {
            $newdata = array(
                'id' => $results->results()[$x]->id,
                'callsign' => $results->results()[$x]->callsign,
                'name' => $results->results()[$x]->name,
                'email' => $results->results()[$x]->email,
                'ifc' => $results->results()[$x]->ifc,
                'rank' => $this->rank($results->results()[$x]->id),
                'status' => $statuses[$results->results()[$x]->status],
                'joined' => $results->results()[$x]->joined,
                'grade' => $results->results()[$x]->grade,
                'viol' => $results->results()[$x]->violand
            );
            $usersarray[$x] = $newdata;
            $x++;
        }

        return $usersarray;

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

}