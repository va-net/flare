<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

/*
This is the main Flare configuration file. Please do not change anything in this unless you know what you are
doing! If updating, please backup this file prior to doing so.
*/

/**
 * @param string $first Beginning of Range
 * @param string $last End of Range
 * @param string $step Amount to Increment
 * @param string $output_format Output Format
 */
function daterange($first, $last, $step = '+1 day', $output_format = 'Y-m-d')
{
    $dates = array();
    $current = strtotime($first);
    $last = strtotime($last);

    while ($current <= $last) {
        $dates[] = date($output_format, $current);
        $current = strtotime($step, $current);
    }

    return $dates;
}

/**
 * @return string
 * @param string $string
 */
function escape($string)
{
    return htmlentities($string, ENT_QUOTES, 'UTF-8');
}
