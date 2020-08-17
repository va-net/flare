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

$GLOBALS['config'] = array(
    'mysql' => array(
        'host' => 'DB_HOST',
        'username' => 'DB_USER',
        'password' => 'DB_PASS',
        'db' => 'DB_NAME'
        ),
    'remember' => array(
        'cookie_name' => 'remember',
        'cookie_expiry' => 604800
    ),
    'session' => array(
        'session_name' => 'user',
        'token_name' => 'token'
    ),
    'va' => array(
        'name' => 'VA_NAME',
        'identifier' => 'VA_IDENTIFIER'
    ),
    'vanet' => array(
        'api_key' => 'VANET_API_KEY',
        'base_url' => 'https://vanet.app'
    ),
    'site' => array(
        'colour_main_hex' => '#E4181E'
    ),
    'defaults' => array(
        'permissions' => '{"admin": 0, "usermanage": 0, "staffmanage": 0, "recruitment": 0, "pirepmanage": 0, "newsmanage":0, "opsmanage": 0, "statsviewing": 0}',
        'fulladminpermissions' => '{"admin": 1, "usermanage": 1, "staffmanage": 1, "recruitment": 1, "pirepmanage": 1, "newsmanage": 1, "opsmanage": 1, "statsviewing": 1}'
    )
);
