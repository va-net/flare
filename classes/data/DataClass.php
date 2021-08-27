<?php

/*
Flare, a fully featured and easy to use crew centre, designed for Infinite Flight.
Copyright (C) 2020  Lucas Rebato

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

interface DataClass
{
    /**
     * @return array
     */
    public static function getAll();

    /**
     * @return object|null
     * @param int $id Object ID
     */
    public static function get($id);

    /**
     * @return bool
     * @param array $obj Object to Create
     */
    public static function create($obj);

    /**
     * @return bool
     * @param int $id Object ID
     * @param array $obj Updated Object
     */
    public static function update($id, $obj);

    /**
     * @return bool
     * @param int $id Object ID
     */
    public static function delete($id);
}
