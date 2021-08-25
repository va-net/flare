<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/
class Events
{
    private static $_listeners = [];

    /**
     * @return string Listener ID
     * @param string $event Event ID
     * @param callback $action Event Action
     */
    public static function listen($event, $action)
    {
        if (!is_callable($action)) {
            throw new Exception("Event Action is Not Callable");
        }
        $id = uniqid("evlis-");
        self::$_listeners[$id] = [
            "event" => $event,
            "action" => $action,
        ];

        return $id;
    }

    /**
     * @return void
     * @param string $listener Listener ID
     */
    public static function unlisten($listener)
    {
        self::$_listeners[$listener]["action"] = null;
        self::$_listeners[$listener]["event"] = null;
    }

    /**
     * @return void
     * @param string $event Event ID
     * @param array $args Event Arguments
     */
    public static function trigger($event, $args = [])
    {
        $params = new Event($event, $args);
        foreach (self::$_listeners as $l) {
            if (($l["event"] == $event || $l["event"] == "*") && $l["action"] != null) {
                call_user_func_array($l["action"], [$params]);
            }
        }
    }
}

class Event
{
    /**
     * @var array
     */
    public $params = [];

    /**
     * @var string
     */
    public $event = '';

    /**
     * @param array $params Event Parameters
     * @param string $event Event ID
     */
    public function __construct($event, $params)
    {
        $this->params = $params;
        $this->event = $event;
    }
}
