<?php

/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

class Notifications {

    /**
     * @var DB
     */
    private static $_db;

    /**
     * @var array
     */
    private static $_events = [
        "user/accepted" => "handleUserAccepted",
        "vanet/event/added" => "handleEventAdded",
        "news/added" => "handleNewsAdded",
        "pirep/accepted" => "handlePirep",
        "pirep/denied" => "handlePirep",
        "user/promoted" => "handlePromotion",
    ];

    private static function init()
    {
        self::$_db = DB::getInstance();
    }

    /**
     * @return array
     * @param int $userId User ID
     */
    public static function mine($userId, $limit = 5)
    {
        self::init();

        if (!is_numeric($limit)) throw new Exception("Parameter Limit must be Numeric");

        $sql = "SELECT *, DATE_FORMAT(CONVERT_TZ(`datetime`, @@session.time_zone, '+00:00'), '%Y-%m-%dT%TZ') AS formattedDate FROM notifications 
                WHERE pilotid=? OR pilotid=0 ORDER BY datetime DESC LIMIT {$limit}";
        
        return self::$_db->query($sql, [$userId])->results();
    }

    /**
     * @return null
     * @param int $pilot Pilot ID
     * @param string $icon Icon
     * @param string $subject Notification Subject
     * @param string $content Notification Content
     */
    public static function notify($pilot, $icon, $subject, $content)
    {
        self::init();

        if (strlen($icon) > 20) throw new Exception("Parameter icon must be 20 Characters or Less");
        if (strlen($subject) > 20) throw new Exception("Parameter subject must be 20 Characters or Less");
        if (strlen($content) > 60) throw new Exception("Parameter content must be 60 Characters or Less");

        $res = self::$_db->insert('notifications', [
            'pilotid' => $pilot,
            'icon' => $icon,
            'subject' => $subject,
            'content' => $content,
        ]);
    }

    /* EVENT HANDLERS */

    /**
     * @return null
     * @param Event $ev
     */
    public static function handleEvent($ev)
    {
        if (!array_key_exists($ev->event, self::$_events)) return;

        call_user_func_array('self::'.self::$_events[$ev->event], [$ev]);
    }

    /**
     * @return null
     * @param Event $ev
     */
    private static function handleUserAccepted($ev)
    {
        $args = $ev->params;
        $vaname = Config::get('va/name');
        $content = "Welcome to {$vaname}! We hope you enjoy your stay.";
        if (strlen($content) > 60) return;
        self::notify($args[0], "fa-smile-beam", "Welcome!", $content);
    }

    /**
     * @return null
     * @param Event $ev
     */
    private static function handleEventAdded($ev)
    {
        $args = $ev->params;
        $content = "An Event Called {$args['Name']} was just posted. Check it out!";
        if (strlen($content) > 60) return;
        self::notify(0, "fa-calendar", "New Event", $content);
    }

    /**
     * @return null
     * @param Event $ev
     */
    private static function handleNewsAdded($ev)
    {
        $args = $ev->params;
        $content = "A News Item titled {$args['subject']} was just added by {$args['author']}";
        if (strlen($content) > 60) return;
        self::notify(0, "fa-newspaper", "News Added", $content);
    }

    /**
     * @return null
     * @param Event $ev
     */
    private static function handlePirep($ev)
    {
        self::init();
        $args = $ev->params;
        if ($ev->event == 'pirep/accepted') {
            $pirep = self::$_db->get('pireps', ['id', '=', $args['id']])->results();
            if (count($pirep) == 0) return;
            $pirep = $pirep[0];
            $content = "Your PIREP from {$pirep->departure} to {$pirep->arrival} was Accepted";
            if (strlen($content) > 60) return;
            self::notify($pirep->pilotid, "fa-check", "PIREP Accepted", $content);
        } else {
            $pirep = self::$_db->get('pireps', ['id', '=', $args['id']])->results();
            if (count($pirep) == 0) return;
            $pirep = $pirep[0];
            $content = "Your PIREP from {$pirep->departure} to {$pirep->arrival} was Denied";
            if (strlen($content) > 60) return;
            self::notify($pirep->pilotid, "fa-times", "PIREP Denied", $content);
        }
    }

    private static function handlePromotion($ev) 
    {
        self::init();
        $usr = (new User)->getUser($ev->params['pilot']);
        $rnk = Rank::find($ev->params['rank']);
        $content = "{$usr->name} was just promoted to {$rnk->name}. Congratulations!";
        if (strlen($content) > 60) {
            $content = "{$usr->name} was just promoted to {$rnk->name}";
            if (strlen($content) > 60) return;
        }
        self::notify(0, "fa-medal", "Promotion", $content);
    }

}