<?php
/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

require_once __DIR__.'/../core/init.php';
header("Content-type: text/css; charset: UTF-8");

?>
@import url('https://fonts.googleapis.com/css2?family=Rubik&display=swap');

@media only screen and (max-width: 991px) {
    .mobile-hidden, .tooltip {
        display: none;
    }
    .main-content {
        width: 100%!important;
        min-height: 80vh;
    }
    .ck-editor__editable_inline {
        min-height: 200px;
    }
}

@media only screen and (min-width: 922px) {
    .desktop-hidden {
        display: none;
    }
    .ck-editor__editable_inline {
        min-height: 300px;
    }
    .publicform {
        margin-left: 20%;
        margin-right: 20%;
        width: 60%;
    }
}

table {
    text-align: center;
}

* {
    font-family: 'Rubik', sans-serif;
}

.nav-link {
    color: white!important;
}

.loaded {
    display: none;
}

.mobile-hidden .panel-link, .mobile-hidden .panel-link:hover {
    color: black!important;
    text-decoration: none;
}

#desktopMenu .panel-link:hover {
    font-weight: bolder;
}

.mobile-hidden .panel-link-dark, .mobile-hidden .panel-link-dark:hover {
    color: #f8f9fa!important;
    text-decoration: none;
}

.modal-dark {
    background-color: #343a40 !important;
}

.divider {
    border-top: 1px solid #000!important;
}

.divider-dark {
    border-top: 1px solid #fff!important;
}

.cursor-pointer {
    cursor: pointer;
}

.navbar-toggler {
	color: rgba(255,255,255,.5);
	border-color: rgba(255,255,255,.1);
}

.bg-custom {
    <?php $textcol = Config::get('TEXT_COLOUR'); ?>
    background-color: <?= Config::get('site/colour_main_hex') ?>!important;
    color: <?= $textcol ?>!important;
}

.navbar-brand {
    color: <?= $textcol ?>!important;
}

#loader {
    position: absolute;
    left: 50%;
    top: 50%;
    z-index: 1;
    width: 150px;
    height: 150px;
    margin: -75px 0 0 -75px;
    width: 120px;
    height: 120px;
}

#loader-wrapper {
    width: 100%;
    min-height: 80vh;
    background-color: #fff;
}

.spinner-custom {
    color: <?= Config::get('site/colour_main_hex') ?>;
}