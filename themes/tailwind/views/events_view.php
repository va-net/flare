<?php
Page::setTitle(Page::$pageData->event['name'] . ' - ' . Page::$pageData->va_name);
require_once __DIR__ . '/../includes/header.php';

$mygate = array_values(array_filter(Page::$pageData->event['slots'], function ($s) {
    return $s['pilotId'] == Page::$pageData->user->data()->ifuserid;
}));
if (count($mygate) < 1) {
    $mygate = null;
} else {
    $mygate = $mygate[0];
}

$avail = count(array_filter(Page::$pageData->event['slots'], function ($x) {
    return $x['pilotId'] == null;
})) != 0;

$pilotdata = array_filter(Page::$pageData->user->getAllUsers(), function ($u) {
    return $u['ifuserid'] != null;
});
$names = array_map(function ($p) {
    return $p['name'];
}, $pilotdata);
$ids = array_map(function ($p) {
    return $p['ifuserid'];
}, $pilotdata);
$pilots = array_combine($ids, $names);
?>
<div id="content" class="m-5 text-black dark:text-white">
    <h1 class="text-3xl font-bold text-center"><?= Page::$pageData->event['name'] ?></h1>
    <p class="mb-3 text-center">
        <?= Page::$pageData->event['description'] ?>
    </p>
    <div class="grid-cols-2 gap-4 space-y-4 md:grid md:space-y-0">
        <div class="p-3 bg-gray-100 border border-gray-200 rounded-md shadow dark:border-0 dark:bg-gray-600">
            <h2 class="text-2xl font-bold">Event Details</h2>
            <ul>
                <li>
                    <b>Local Time:</b>
                    <span x-text="new Date('<?= Page::$pageData->event['date'] ?>Z').toLocaleString()"></span>
                    <span class="hidden md:inline">(<?= str_replace('T', ' ', Page::$pageData->event['date']) ?> UTC)</span>
                </li>
                <li>
                    <b>Departure:</b>
                    <?= Page::$pageData->event['departureIcao'] ?>
                </li>
                <li>
                    <b>Arrival:</b>
                    <?= Page::$pageData->event['arrivalIcao'] ?>
                </li>
                <li>
                    <b>Aircraft:</b>
                    <?= Page::$pageData->event['aircraft']['aircraftName'] ?>
                    (<?= Page::$pageData->event['aircraft']['liveryName'] ?>)
                </li>
                <li>
                    <b>Server:</b>
                    <?= ucfirst(Page::$pageData->event['server']) ?>
                </li>
            </ul>
        </div>
        <div class="p-3 bg-gray-100 border border-gray-200 rounded-md shadow dark:border-0 dark:bg-gray-600">
            <h2 class="text-2xl font-bold">
                Aircraft Details
                <span class="hidden md:inline">- <?= Page::$pageData->event['aircraft']['aircraftName'] ?></span>
            </h2>
            <ul>
                <li>
                    <b>Max. Takeoff Weight:</b>
                    <?= Page::$pageData->event['aircraft']['maxTakeoffWeight'] ?>kg
                </li>
                <li>
                    <b>Max. Landing Weight:</b>
                    <?= Page::$pageData->event['aircraft']['maxLandingWeight'] ?>kg
                </li>
                <li>
                    <b>Never Exceed Speed:</b>
                    <?= Page::$pageData->event['aircraft']['neverExceed'] ?>
                </li>
                <li>
                    <b>Service Ceiling:</b>
                    <?= Page::$pageData->event['aircraft']['serviceCeiling'] ?>
                </li>
                <li>
                    <b>Range:</b>
                    <?= Page::$pageData->event['aircraft']['range'] ?>NM
                </li>
                <li>
                    <b>Normal Approach Speed:</b>
                    <?= Page::$pageData->event['aircraft']['apprSpeedRef'] ?>kts
                </li>
                <li>
                    <b>Max. Passengers:</b>
                    <?= Page::$pageData->event['aircraft']['maxPassengers'] ?>
                </li>
            </ul>
        </div>
        <div class="col-span-2 p-3 bg-gray-100 border border-gray-200 rounded-md shadow dark:border-0 dark:bg-gray-600">
            <div class="flex items-center mb-2">
                <h2 class="flex-1 text-2xl font-bold">Gates</h2>
                <?php if ($mygate == null && $avail) : ?>
                    <button class="flex-none px-3 py-2 rounded-md shadow-md bg-primary text-primary-text focus:outline-none focus:ring-2 focus:ring-transparent focus:ring-offset-1 focus:ring-offset-black dark:focus:ring-offset-white" @click="toggleEventSignup('<?= Page::$pageData->event['id'] ?>')">
                        Sign Up
                    </button>
                <?php elseif ($mygate != null) : ?>
                    <button class="flex-none px-3 py-2 rounded-md shadow-md bg-primary text-primary-text focus:outline-none focus:ring-2 focus:ring-transparent focus:ring-offset-1 focus:ring-offset-black dark:focus:ring-offset-white" @click="toggleEventSignup('<?= Page::$pageData->event['id'] ?>')">
                        Pull Out
                    </button>
                <?php endif; ?>
            </div>
            <div class="overflow-hidden border-b border-gray-200 shadow dark:border-gray-400 sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-400">
                    <thead class="bg-primary text-primary-text">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left uppercase">
                                Gate
                            </th>
                            <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left uppercase">
                                Pilot
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-500">
                        <?php foreach (Page::$pageData->event['slots'] as $gate) : ?>
                            <?php
                            $pilotName = $gate['pilotName'];
                            if ($pilotName === '' && isset($pilots[$gate['pilotId']])) {
                                $pilotName = $pilots[$gate['pilotId']];
                            } elseif ($pilotName === '') {
                                $pilotName = $gate['pilotId'];
                            }
                            ?>
                            <tr>
                                <td class="px-6 py-3 whitespace-nowrap">
                                    <?= $gate['gate'] ?>
                                </td>
                                <td class="px-6 py-3 whitespace-nowrap">
                                    <?= $pilotName ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>