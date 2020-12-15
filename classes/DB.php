<?php

/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

class DB 
{

    private static $_instance = null;
    private $_pdo, 
            $_query, 
            $_error = false, 
            $_results, 
            $_count = 0;

    private function __construct() 
    {

        try {
            $this->_pdo = new PDO('mysql:host='.Config::get('mysql/host').';dbname='.Config::get('mysql/db').';port='.Config::get('mysql/port'), Config::get('mysql/username'), Config::get('mysql/password'));
        } catch(PDOException $e) {
            die($e->getMessage());
        }

    }

    /**
     * @return DB
     */
    public static function getInstance() 
    {

        if (!isset(self::$_instance)) {
            self::$_instance = new DB();
        }
        return self::$_instance;

    }

    /**
     * @return DB
     */
    public static function newInstance() 
    {

        return new DB();

    }
    /**
     * @return DB
     * @param string $sql SQL to Run
     * @param array $params Prepared Statement Parameters
     */
    public function query($sql, $params = array())
    {

        $this->_error = false;
        if ($this->_query = $this->_pdo->prepare($sql)) {
            $x = 1;
            if (count($params)) {
                foreach ($params as $param) {
                    $this->_query->bindValue($x, strip_tags($param));
                    $x++;
                }
            }

            if ($this->_query->execute()) {
                $this->_results = $this->_query->fetchAll(PDO::FETCH_OBJ);
                $this->_count = $this->_query->rowCount();
            } else {
                $this->_error = true;
                $this->_results = [];
                $this->_count = 0;
            }

        }

        return $this;

    }

    /**
     * @return DB
     * @param string $table Table Name
     */
    public function getTable($table)
    {

        $sql = "SELECT * FROM {$table}";
        if (!$this->query($sql)->error()) {
            return $this;
        }
        return $this;

    }

    private function action($action, $table, $where = array(), $order = false) 
    {
    
        if (count($where) === 3) {
            $operators = array('=', '<', '>', '<=', '>=');

            $field = $where[0];
            $operator = $where[1];
            $value = $where[2];

            if (in_array($operator, $operators)) {
                $sql = "{$action} FROM {$table} WHERE {$field} {$operator} ?";

                if ($order) {
                    $orderby = $order[0];
                    $direction = $order[1];
                    $sql .= " ORDER BY {$orderby} {$direction}";
                }

                if (!$this->query($sql, array($value))->error()) {
                    return $this;
                }
            }
        }

        return $this;
        
    }

    /**
     * @return bool
     */
    public function error()
    {

        return $this->_error;
        
    }

    /**
     * @return DB
     * @param string $table Table Name
     * @param array $where Where Clause
     * @param bool|array $order Order Clause
     */
    public function get($table, $where, $order = false)
    {

        return $this->action('SELECT *', $table, $where, $order);

    }

    /**
     * @return DB
     * @param string $table Table Name
     * @param array $where Where Clause
     */
    public function delete($table, $where) 
    {

        return $this->action('DELETE', $table, $where);

    }

    /**
     * @return DB
     * @param string $table Table Name
     * @param array $fields Field Names and Values
     */
    public function insert($table, $fields = array())
    {

        $keys = array_keys($fields);
        $values = '';
        $x = 1;

        foreach($fields as $field) {
            $values .= '?';

            if ($x < count($fields)) {
                $values .= ', ';
            }
            $x++;
        }

        $sql = "INSERT INTO {$table} (`" . implode('`, `', $keys)  . "`) VALUES ({$values})";

        if (!$this->query($sql, $fields)->error()) {
            return $this;
        }

        return $this;

    }

    /**
     * @return DB
     * @param string $table Table Name
     * @param int|string $id ID of Row
     * @param string $where ID Column Name
     * @param array $fields Updated Field Names and Values
     */
    public function update($table, $id, $where, $fields = array())
    {

        $set = '';
        $x = 1;

        foreach($fields as $name => $value) {
            $set .= "{$name} = ?";

            if ($x < count($fields)) {
                $set .= ', ';
            }
            $x++;
        }

        $sql = "UPDATE {$table} SET {$set} WHERE {$where} = {$id}";

        if (!$this->query($sql, $fields)->error()) {
            return $this;
        }

        return $this;

    }

    /**
     * @return int
     */
    public function count()
    {

        return $this->_count;

    }

    /**
     * @return array
     */
    public function results()
    {

        return $this->_results;

    }

    /**
     * @return object|bool
     */
    public function first()
    {

        if ($this->count() > 0) {
            return $this->results()[0];
        }

        return false;

    }

    /**
     * @return DB
     * @param string $table Table Name
     */
    public function getAll($table) 
    {

        return $this->query("SELECT * FROM {$table}");

    }

}