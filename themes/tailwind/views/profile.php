<?php
Page::setTitle('Profile - ' . Page::$pageData->va_name);
require_once __DIR__ . '/../includes/header.php';
$user = Page::$pageData->user->data();

$pirepcols = [
    'callsign' => 'Callsign',
    'pilotname' => 'Pilot',
    'date' => 'Date',
    'fnum' => 'Flight Number',
    'route' => 'Route',
    'departure' => 'Departure',
    'arrival' => 'Arrival',
    'aircraft' => 'Aircraft',
    'status' => 'Status',
    'multiplier' => 'Multiplier',
    'flighttime' => 'Flight Time',
];
$usercols = [
    'callsign' => 'Callsign',
    'name' => 'Name',
    'email' => 'Email',
    'flighttime' => 'Flight Time',
    'rank' => 'Rank',
    'ifc' => 'IFC Username',
    'notes' => 'Notes',
    'joined' => 'Date Joined',
    'violand' => 'Violation/Landing Ratio',
    'grade' => 'Grade',
    'flags' => 'Watchlist Flags',
];
$tables = [
    [
        'name' => 'My PIREPs',
        'id' => 'my-pireps',
        'columns' => array_filter($pirepcols, function ($x) {
            return $x != 'callsign' && $x != 'pilotname';
        }, ARRAY_FILTER_USE_KEY),
        'permission' => null,
    ],
    [
        'name' => 'PIREPs Admin',
        'id' => 'pireps-admin',
        'columns' => $pirepcols,
        'permission' => 'pirepmanage',
    ],
    [
        'name' => 'Pending PIREPs',
        'id' => 'pending-pireps',
        'columns' => $pirepcols,
        'permission' => 'pirepmanage',
    ],
    [
        'name' => 'Routes Admin',
        'id' => 'routes-admin',
        'columns' => [
            'fltnum' => 'Flight Number',
            'route' => 'Route',
            'dep' => 'Departure',
            'arr' => 'Arrival',
            'duration' => 'Flight Time',
            'notes' => 'Notes',
        ],
        'permission' => 'opsmanage',
    ],
    [
        'name' => 'Users Admin',
        'id' => 'users-admin',
        'columns' => $usercols,
        'permission' => 'usermanage',
    ],
    [
        'name' => 'Recruitment',
        'id' => 'recruitment',
        'columns' => $usercols,
        'permission' => 'recruitment',
    ]
];

$userpermissions = Permissions::forUser($user->id);
$usertables = [];
foreach ($tables as $table) {
    if ($table['permission'] == null || in_array($table['permission'], $userpermissions)) {
        $usertables[] = $table;
    }
}
?>
<script id="tables" type="application/json">
    <?= Json::encode($usertables) ?>
</script>
<script>
    var tables = JSON.parse(document.getElementById('tables').innerHTML);

    function onTableCheckboxChange(e, value, columns, setColumns) {
        if (e.target.checked) {
            columns.push(value);
        } else {
            columns = columns.filter(x => x != value);
        }

        setColumns(columns);
    }

    function customizeOrReset(tableId, useDefault, setUseDefault, setColumns) {
        if (useDefault) {
            setUseDefault(false);
            setColumns([]);
        } else {
            setUseDefault(true);
            setColumns(null);
        }
    }
</script>

<div id="content" class="grid-cols-2 gap-4 m-5 space-y-4 text-black md:grid md:space-y-0 dark:text-white">
    <div class="p-3 bg-gray-100 border border-gray-200 rounded-md shadow dark:border-0 dark:bg-gray-600">
        <h2 class="text-2xl font-bold">Edit Profile</h2>
        <form method="post" class="space-y-2" id="edit-profile">
            <div class="space-y-1">
                <label for="name">Name</label>
                <input id="name" name="name" type="text" value="<?= escape($user->name) ?>" required class="form-control" />
            </div>
            <div class="space-y-1">
                <label for="name">Callsign</label>
                <input id="callsign" name="callsign" type="text" value="<?= escape($user->callsign) ?>" required class="form-control" />
            </div>
            <div class="space-y-1">
                <label for="email">Email</label>
                <input id="email" name="email" type="email" value="<?= escape($user->email) ?>" class="form-control" />
            </div>
            <div class="space-y-1">
                <label for="ifc">IFC URL</label>
                <input id="ifc" name="ifc" type="url" value="<?= escape($user->ifc) ?>" required class="form-control" />
            </div>
        </form>
        <button type="submit" form="edit-profile" class="px-3 py-2 mt-3 rounded-md shadow-md bg-primary text-primary-text focus:outline-none focus:ring-2 focus:ring-transparent focus:ring-offset-1 focus:ring-offset-black dark:focus:ring-offset-white">
            Save
        </button>
    </div>
    <div class="p-3 bg-gray-100 border border-gray-200 rounded-md shadow dark:border-0 dark:bg-gray-600">
        <h2 class="text-2xl font-bold">Change Password</h2>
        <?php if (!empty(Page::$pageData->user->data()->password)) : ?>
            <form method="post" action="/home" class="space-y-2" id="change-password">
                <input type="hidden" class="hidden" name="action" value="changepass" />
                <div class="space-y-1">
                    <label for="oldpass">Old Password</label>
                    <input id="oldpass" name="oldpass" type="password" required class="form-control" />
                </div>
                <div class="space-y-1">
                    <label for="newpass">New Password</label>
                    <input id="newpass" name="newpass" type="password" minlength="8" required class="form-control" />
                </div>
                <div class="space-y-1">
                    <label for="confpass">Confirm New Password</label>
                    <input id="confpass" name="confpass" type="password" minlength="8" required class="form-control" />
                </div>
            </form>
            <button type="submit" form="change-password" class="px-3 py-2 mt-3 rounded-md shadow-md bg-primary text-primary-text focus:outline-none focus:ring-2 focus:ring-transparent focus:ring-offset-1 focus:ring-offset-black dark:focus:ring-offset-white">
                Save
            </button>
        <?php else : ?>
            <p class="font-semibold text-red-500">You are using VANet to log in. A password is not required, nor is one stored.</p>
        <?php endif; ?>
    </div>
    <div class="p-3 bg-gray-100 border border-gray-200 rounded-md shadow dark:border-0 dark:bg-gray-600">
        <h2 class="mb-2 text-2xl font-bold">Your Awards</h2>
        <?php if (count(Page::$pageData->awards) < 1) : ?>
            <p>None Yet!</p>
        <?php else : ?>
            <ul>
                <?php foreach (Page::$pageData->awards as $a) : ?>
                    <li>
                        <img src="<?= $a->imageurl ?>" style="height: 25px; width: auto; display: inline;" />
                        <?= $a->name ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
    <div class="p-3 bg-gray-100 border border-gray-200 rounded-md shadow dark:border-0 dark:bg-gray-600">
        <h2 class="mb-1 text-2xl font-bold">VANet Account</h2>
        <p class="mb-3">
            <?= Page::$pageData->va_name ?> partners with
            <a href="https://vanet.app" target="_blank" class="font-semibold hover:underline">VANet</a>
            for various services. You can now link your VANet account to Flare in order to log in simply
            and more securely.
        </p>
        <?php if (Page::$pageData->user->data()->vanet_id && !empty(Page::$pageData->user->data()->email)) : ?>
            <p>
                <span class="font-bold">Your VANet account is linked.</span>
                You can now choose to delete all sensitive data from this Crew Center.
                This will mean that you must use VANet to log in instead of your email
                and password.
            </p>
            <form method="post" action="/home">
                <input type="hidden" class="hidden" name="action" value="delsensitive" />
                <button type="submit" class="px-3 py-2 mt-3 rounded-md shadow-md bg-red-500 text-white focus:outline-none focus:ring-2 focus:ring-transparent focus:ring-offset-1 focus:ring-offset-black dark:focus:ring-offset-white">
                    Delete sensitive data
                </button>
            </form>
        <?php elseif (Page::$pageData->user->data()->vanet_id && empty(Page::$pageData->user->data()->email)) : ?>
            <p>
                <span class="font-bold">Your VANet account is linked and no sensitive data is stored in our database.</span>
                You must use VANet to log in instead of your email and password.
            </p>
        <?php else : ?>
            <p>
                <span class="font-bold">Your VANet account is not linked.</span>
                You can now choose to link your VANet account to Flare in order to log in simply
                and more securely.
            </p>
            <a href="/oauth/login" class="button-primary inline-block">Link VANet</a>
        <?php endif; ?>
    </div>
    <div class="p-3 bg-gray-100 border border-gray-200 rounded-md shadow dark:border-0 dark:bg-gray-600 col-span-2">
        <h2 class="mb-1 text-2xl font-bold">Customize Tables</h2>
        <div class="w-full space-y-1" x-data="{ openItem: null }">
            <template x-for="table in tables" :key="table.id">
                <div x-data="{ useDefault: localStorage.getItem(`table__${table.id}`) == null, columns: localStorage.getItem(`table__${table.id}`) ? JSON.parse(localStorage.getItem(`table__${table.id}`)) : null }" x-effect="if (columns === null) {localStorage.removeItem(`table__${table.id}`)} else {localStorage.setItem(`table__${table.id}`, JSON.stringify(columns))}">
                    <div class="bg-primary text-primary-text bg-opacity-50 w-full p-2 font-semibold cursor-pointer flex" @click="openItem = openItem == table.id ? null : table.id">
                        <p class="flex-1" x-text="table.name"></p>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" x-show="openItem != table.id">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" x-show="openItem == table.id">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7" />
                        </svg>
                    </div>
                    <div class="dark:bg-white/20 bg-transparent shadow border border-gray-600 dark:border-0 p-2" x-show="openItem == table.id">
                        <p>
                            Using <span x-text="useDefault ? 'default' : 'custom'"></span> columns - click
                            <span class="font-semibold hover:underline cursor-pointer" @click="customizeOrReset(table.id, useDefault, (v) => {useDefault = v}, (v)=>{columns=v})">here</span> to
                            <span x-text="useDefault ? 'customize' : 'reset'"></span>
                        </p>
                        <template x-for="[id, name] in Object.entries(table.columns)" :key="id">
                            <div x-show="!useDefault">
                                <input type="checkbox" @change="onTableCheckboxChange($event, id, columns, (v) => {columns=v})" :checked="columns?.includes(id)" :name="`${table.id}:${id}`" :id="`${table.id}:${id}`" /> <label :for="`${table.id}:${id}`" x-text="name"></label>
                            </div>
                        </template>
                    </div>
                </div>
            </template>
        </div>
    </div>
    <?php require_once __DIR__ . '/../includes/footer.php'; ?>