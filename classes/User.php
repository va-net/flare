<?php

class User
{

    private $_db,
            $_data,
            $_sessionName,
            $_cookieName,
            $_isLoggedIn;


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
                   //logout
               }
            }
        } else {
            $this->find($user);
        }

    }

    public function create($fields) 
    {

        if (!$this->_db->insert('pilots', $fields)) {
            throw new Exception('There was a problem creating an account.');
        }

        $result = $this->_db->get('pilots', array('email', '=', $fields['email']));
        $userId = $result->first()->id;

        $this->_db->insert('profile_pics', array(
            'user_id' => $userId,
            'status' => 0
        ));

    }

    public function find($user = null) 
    {

        if ($user) {
            $field = (is_numeric($user)) ? 'id' : 'email';
            $data = $this->_db->get('pilots', array($field, '=', $user));
            if ($data->count()) {
                $this->_data = $data->first();
                return true;
            }
        }
        return false;

    }

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
        
        return false;

    }

    public function exists()
    {

        return (!empty($this->_data)) ? true : false;

    }

    public function data() 
    {

        return $this->_data;

    }

    public function isLoggedIn() 
    {

        return $this->_isLoggedIn;

    }

    public function logout() 
    {

        Session::delete($this->_sessionName);
        Cookie::delete($this->_cookieName);

    }

    public function update($fields = array(), $id = null) 
    {

        if (!$id && $this->isLoggedIn()) {
            $id = $this->data()->id;
        }

        if (!$this->_db->update('pilots', $id, 'id', $fields)) {
            throw new Exception('There was a problem updating the user.');
        }

    }

    public function hasPermission($key, $id = null) 
    {

        if (!$id && $this->isLoggedIn()) {
            $id = $this->data()->id;
        }
        
        $user = $this->_db->get('pilots', array('id', '=', $id));

        $permissions = json_decode($user->first()->permissions, true);
        
        if (array_key_exists($key, $permissions)) {
            if ($permissions[$key] == true) {
                return true;
            }
        }
        return false;

    }

    public function fullName() 
    {

        return $this->data()->firstName. ' ' .$this->data()->lastName;

    }

    public function getProfilePic($id = null) 
    {

        if (!$id && $this->isLoggedIn()) {
            $id = $this->data()->id;  
        }

        $result = $this->_db->get('profile_pics', array('user_id', '=', $id));

        if (!$result->first()->status) {
            return './assets/user/profile_pics/default.jpg';
        }

        return './assets/user/profile_pics/'.$result->first()->id.'.jpeg';

    }

    public function setProfilePic($file, $id = null) 
    {

        if (!$id && $this->isLoggedIn()) {
            $id = $this->data()->id;
        }

        $uniqueid = Image::save($file, './assets/user/profile_pics/');

        if($uniqueid === false) {
            return false;
        }
        
        if(!$this->_db->update('profile_pics', $id, 'user_id', array('status' => 1, 'id' => $uniqueid))) {
            return false;
        }
        return true;

    }

    public function rank($id = null) 
    {

        if (!$id && $this->isLoggedIn()) {
            $id = $this->data()->id;
        }

        $result = $this->_db->get('pilots', array('id', '=', $id));
        $time = $result->first()->transhours;

        $pireps = Pirep::fetchApprovedPireps($id);

        if ($pireps->count() != 0) {
            foreach (range(0, $pireps->count() - 1) as $i) {
                $time = $time + $pireps->results()[$i]->flighttime;
            }
        } else {
            $time = 0;
        }

        return Rank::calc($time);

    }

    public function getFlightTime($id = null)
    {

        if (!$id && $this->isLoggedIn()) {
            $id = $this->data()->id;
        }

        $result = $this->_db->get('pilots', array('id', '=', $id));
        $time = $result->first()->transhours;
        $pireps = Pirep::fetchApprovedPireps($id);

        if ($pireps->count() != 0) {
            foreach (range(0, $pireps->count() - 1) as $i) {
                $time = $time + $pireps->results()[$i]->flighttime;
            }
        } else {
            $time = 0;
        }

        return $time;

    }

    public function pireps($id = null)
    {

        if (!$id && $this->isLoggedIn()) {
            $id = $this->data()->id;
        }

        return Pirep::totalFiled($id);

    }


    public function fetchPireps($id)
    {

        if (!$id) {
            return $this->_db->getAll('pireps');
        }

        return $this->_db->get('pireps', array('id', '=', $id));

    }

}