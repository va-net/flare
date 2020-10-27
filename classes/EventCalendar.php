<?php

/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

use Eluceo\iCal\Domain\Entity\Calendar;
use Eluceo\iCal\Domain\ValueObject\Date;
use Eluceo\iCal\Domain\ValueObject\SingleDay;
use Eluceo\iCal\Domain\ValueObject\Location;
use Eluceo\iCal\Presentation\Factory\CalendarFactory;

class EventCalendar {

    /**
     * @return null
     * @param string $name Name of the event
     * @param string $description Description of the event
     * @param string $dateTime Date and time of event in 'Y-m-d H:m' format
     * @param string $from ICAO code
     * @param string $to ICAO code
     */
    public static function createAndDownloadEvent($name, $description, $dateTime, $from, $to)
    {

        $event = new Eluceo\iCal\Domain\Entity\Event();
        $location = new Location($from.' - '.$to);
        $event
            ->setSummary($name)
            ->setDescription($description)
            ->setLocation($location)
            ->setOccurrence(
                new SingleDay(
                    new Date(DateTimeImmutable::createFromFormat('Y-m-d H:m', $dateTime))
                )
            );

        $calendar = new Calendar([$event]);

        $componentFactory = new CalendarFactory();
        $calendarComponent = $componentFactory->createCalendar($calendar);
        
        header('Content-Type: text/calendar; charset=utf-8');
        header('Content-Disposition: attachment; filename="'.$name.'.ics"');
        
        echo $calendarComponent;
    }

}