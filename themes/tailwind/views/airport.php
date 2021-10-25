<?php
Page::setTitle(Page::$pageData->airport['icaoCode'] . ' - ' . Page::$pageData->va_name);
require_once __DIR__ . '/../includes/header.php';
?>
<div id="content" class="m-6 text-black dark:text-white" x-data="{ activeTab: 'general' }">
    <h1 class="mb-5 text-4xl font-bold block">Airport Information - <?= Page::$pageData->airport['icaoCode'] ?></h1>
    <div class="flex gap-3 border-b border-gray-200 dark:border-gray-500 mb-3">
        <template x-for="tab in ['General', 'PIREPs', 'Routes']" :key="tab.generateId()">
            <button :class="`${activeTab == tab.generateId() ? 'border-b-2 border-primary' : 'border-b-2 border-transparent hover:border-black dark:hover:border-white'} pb-1 px-2 mb-[-0.0625rem] cursor-pointer flex items-center font-semibold text-lg`" @click="activeTab = tab.generateId()">
                <span x-text="tab" class="flex-1"></span>
            </button>
        </template>
    </div>
    <div id="general" x-show="activeTab == $el.id">
        <div class="lg:grid grid-cols-2 gap-4 space-y-4 lg:space-y-0">
            <div class="bg-gray-100 dark:bg-white/10 rounded p-3 shadow">
                <h2 class="text-2xl font-bold mb-3">Basic Information</h2>
                <ul>
                    <li><span class="font-bold">Name:</span> <?= Page::$pageData->airport['name'] ?></li>
                    <li><span class="font-bold">ICAO:</span> <?= Page::$pageData->airport['icaoCode'] ?></li>
                    <li><span class="font-bold">IATA:</span> <?= Page::$pageData->airport['iataCode'] ?? 'N/A' ?></li>
                    <li><span class="font-bold">Country:</span> <?= Page::$pageData->airport['country'] ?? 'N/A' ?></li>
                    <li><span class="font-bold">City:</span> <?= Page::$pageData->airport['city'] ?? 'N/A' ?></li>
                    <li><span class="font-bold">Elevation:</span> <?= Page::$pageData->airport['elevation'] ?> ft</li>
                </ul>
            </div>
            <div class="bg-gray-100 dark:bg-white/10 rounded p-3 shadow" x-data="{ data: { atc: undefined, atis: undefined } }" x-init="initairport(data, '<?= Page::$pageData->airport['icaoCode'] ?>')">
                <h2 class="text-2xl font-bold mb-2">Live Data - Expert Server</h2>
                <?php if (Page::$pageData->is_gold) : ?>
                    <h3 class="text-lg font-bold mb-1">ATC Stations</h3>
                    <div class="table-wrapper mb-3">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Station</th>
                                    <th>Controller</th>
                                </tr>
                            </thead>
                            <tbody x-html="airportAtcTable(data.atc, '<?= Page::$pageData->airport['icaoCode'] ?>')"></tbody>
                        </table>
                    </div>
                <?php endif; ?>
                <h3 class="text-lg font-bold mb-1">ATIS</h3>
                <p x-text="data.atis === undefined ? 'Loading...' : data.atis === null ? 'No ATIS' : data.atis"></p>
            </div>
            <div class="bg-gray-100 dark:bg-white/10 rounded p-3 shadow">
                <h2 class="text-2xl font-bold mb-3">ATC Facilities</h2>
                <ul>
                    <?php foreach (Page::$pageData->airport['frequencies'] as $f) : ?>
                        <li><span class="font-bold"><?= $f['description'] ?>:</span> <?= $f['frequency'] ?>MHz</li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="bg-gray-100 dark:bg-white/10 rounded p-3 shadow">
                <h2 class="text-2xl font-bold mb-3">Runways</h2>
                <ul>
                    <?php foreach (Page::$pageData->airport['runways'] as $r) : ?>
                        <li><span class="font-bold"><?= $r['identL'] ?>/<?= $r['identH'] ?>:</span> <?= $r['length'] ?>ft</li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
    <div id="pireps" x-show="activeTab == $el.id">
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th class="hidden lg:table-cell">Pilot</th>
                        <th class="hidden lg:table-cell">Date</th>
                        <th>Route</th>
                        <th>Flight Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (Page::$pageData->pireps as $pirep) : ?>
                        <tr>
                            <td class="hidden lg:table-cell"><?= $pirep->pilotname ?></td>
                            <td x-text="new Date('<?= $pirep->date ?>').toLocaleDateString()" class="hidden lg:table-cell"></td>
                            <td><?= $pirep->departure ?>-<?= $pirep->arrival ?></td>
                            <td><?= Time::secsToString($pirep->flighttime) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div id="routes" x-show="activeTab == $el.id">
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th class="hidden lg:table-cell">Flight Number</th>
                        <th>Departure</th>
                        <th>Arrival</th>
                        <th class="hidden lg:table-cell">Flight Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (Page::$pageData->routes as $route) : ?>
                        <tr>
                            <td class="hidden lg:table-cell"><?= $route->fltnum ?></td>
                            <td><?= $route->dep ?></td>
                            <td><?= $route->arr ?></td>
                            <td class="hidden lg:table-cell"><?= Time::secsToString($route->duration) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>