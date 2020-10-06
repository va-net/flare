<?php

class Events {
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
     * @return null
     * @param string $listener Listener ID
     */
    public static function unlisten($listener)
    {
        self::$_listeners[$listener]["action"] = null;
        self::$_listeners[$listener]["event"] = null;
    }

    /**
     * @return null
     * @param string $event Event ID
     * @param array $args Event Arguments
     */
    public static function trigger($event, $args = []) 
    {
        $params = new Event($event, $args);
        foreach (self::$_listeners as $l) {
            if (($l["event"] == $event || $l["event"] == "*" || preg_match($l["event"], $event) == 1) && $l["action"] != null) {
                call_user_func_array($l["action"], [$params]);
            }
        }
    }
}

class Event {
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