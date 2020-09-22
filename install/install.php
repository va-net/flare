<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

require __DIR__.'/../core/init.php';
require_once 'Installer.php';

Page::setTitle('Install Flare');

switch (Input::get('page')) {

    case 'va-details':
    case '':
        Installer::showTemplate('va-details');
        break;
    case 'va-details-complete':
        if (!VANet::isVeKey(Input::get('vanet-api'))) {
            Session::flash('error', 'Hmm, that seems to be a Personal VANet API Key. Ensure you are using your VA/VO one, you can find it <a href="https://vanet.app/airline/profile" target="_blank">here</a>.');
            Redirect::to('?page=va-details');
            die();
        }
        if (!Installer::createConfig(array(
            'VA_NAME' => escape(Input::get('va-name')),
            'VA_IDENTIFIER' => escape(Input::get('va-ident')),
            'VANET_API_KEY' => escape(Input::get('vanet-api'))
        ))) {
            Session::flash('error', 'Whoops! Something went wrong. Please try again.');
            Redirect::to('?page=va-details');
        }

        Redirect::to('?page=db-setup');
        break;
    case 'db-setup':
        Installer::showTemplate('db-setup');
        break;
    case 'db-install':
        if (!Installer::appendConfig(array(
            'DB_HOST' => Input::get('db-host'),
            'DB_USER' => Input::get('db-user'),
            'DB_PASS' => Input::get('db-pass'),
            'DB_NAME' => Input::get('db-name'),
            'DB_PORT' => Input::get('db-port')
        ))) {
            Session::flash('error', 'Whoops! Flare couldn\'t connect to the database. Please try again.');
            Redirect::to('?page=db-setup');
        }
        Redirect::to('?page=db-setup-cont');
        break;
    case 'db-setup-cont':
        Installer::showTemplate('db-setup-cont');
        if (Input::get('submit')) {
            sleep(8);
            if (!Installer::setupDb()) {
                Session::flash('error', 'Hmm. Looks like there was an error setting up the database. Ensure you have entered the correct database details, and try again.');
                Redirect::to('?page=db-setup');
            }
            Redirect::to('?page=user-setup');
        }
        break;
    case 'user-setup':
        Installer::showTemplate('user-setup');
        break;
    case 'user-setup-complete':
        require_once '../core/init.php';
        $validate = new Validate();
        $validation = $validate->check($_POST, array(
            'password' => array(
                'matches' => 'password-repeat'
            )
        ));
        if ($validation->passed()) {
            $user = new User();
            try {
                $user->create(array(
                    'name' => Input::get('name'),
                    'email' => Input::get('email'),
                    'ifc' => Input::get('ifc'),
                    'password' => Hash::make(Input::get('password')),
                    'callsign' => Input::get('callsign'),
                    'status' => 1
                ));
                Permissions::giveAll(1);
            } catch(Exception $e) {
                Session::flash('error', 'Something went wrong when trying to register the user. Please try again.');
                Redirect::to('?page=user-setup');
            }
            Redirect::to('?page=success');
        }
        break;
    case 'success':
        Installer::showTemplate('success');

}


