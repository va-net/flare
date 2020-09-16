<?php

/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

class Permissions
{

    private static $_permissions = array(
        'usermanage' => array(
            'name' => 'User Management',
            'icon' =>'fa-users'
        ), 
        'staffmanage' => array(
            'name' => 'Staff Management',
            'icon' => 'fa-user-shield'
        ),
        'recruitment' => array(
            'name' => 'Recruitment',
            'icon' => 'fa-id-card'
        ), 
        'pirepmanage' => array(
            'name' => 'PIREP Management',
            'icon' => 'fa-plane'
        ), 
        'newsmanage' => array(
            'name' => 'News Management',
            'icon' => 'fa-newspaper'
        ), 
        'emailpilots' => array(
            'name' => 'Email Pilots',
            'icon' => 'fa-envelope'
        ), 
        'opsmanage' => array(
            'name' => 'Operations Management',
            'icon' => 'fa-file-alt'
        ), 
        'statsviewing' => array(
            'name' => 'VA Statistics',
            'icon' => 'fa-chart-pie'
        )
    );

    /**
     * @return array
     */
    public static function getAll()
    {

        return self::$_permissions;

    }

}