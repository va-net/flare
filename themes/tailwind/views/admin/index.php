<?php
Page::setTitle('Site Dashboard - ' . Page::$pageData->va_name);
require_once __DIR__ . '/../../includes/header.php';
$days = Page::$pageData->days;
?>
<script>
    var color = localStorage.getItem('darkMode') == 1 ? '#fff' : '#000';
    Chart.defaults.color = color;
</script>
<div id="content" class="text-black dark:text-white" x-init="$watch('isDarkMode', () => location.reload())">
    <div class="flex w-full p-5 dark:bg-gray-600 bg-gray-100 py-7 mb-4 items-center">
        <h2 class="flex-1 text-2xl font-bold lg:text-4xl">
            Site Dashboard
        </h2>
        <form class="block flex-none" method="get">
            <input class="form-control w-auto max-w-[150px] lg:max-w-none placeholder-white" name="days" type="number" placeholder="Days" value="<?= $days ?>" />
        </form>
    </div>
    <div class="z-10 flex-row w-full gap-5 px-5 space-y-3 text-black md:flex md:space-y-0 mb-7">
        <div class="flex items-center flex-auto w-full p-4 bg-white border rounded shadow-lg min-h-20">
            <div class="flex w-12 h-12 mr-3 text-white bg-red-600 rounded-full">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 m-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
            </div>
            <div>
                <h3 class="text-lg font-bold">PIREPs (<?= $days ?> Days)</h3>
                <h5 class="font-semibold"><?= Page::$pageData->pireps ?></h5>
            </div>
        </div>
        <div class="flex items-center flex-auto w-full p-4 bg-white border rounded shadow-lg min-h-20">
            <div class="flex w-12 h-12 mr-3 text-white bg-blue-600 rounded-full">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 m-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                </svg>
            </div>
            <div>
                <h3 class="text-lg font-bold">Flight Time (<?= $days ?> Days)</h3>
                <h5 class="font-semibold"><?= Page::$pageData->hrs ?></h5>
            </div>
        </div>
        <div class="flex items-center flex-auto w-full p-4 bg-white border rounded shadow-lg min-h-20">
            <div class="flex w-12 h-12 mr-3 text-white bg-green-500 rounded-full">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 m-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                </svg>
            </div>
            <div>
                <h3 class="text-lg font-bold">New Pilots (<?= $days ?> Days)</h3>
                <h5 class="font-semibold">
                    <?= Page::$pageData->pilots ?>
                </h5>
            </div>
        </div>
    </div>
    <div class="lg:grid grid-cols-2 gap-4 mx-5 space-y-4 lg:space-y-0">
        <div class="dark:bg-white/10 bg-gray-100 rounded p-3 shadow">
            <h2 class="text-2xl font-bold">PIREPs (<?= $days ?> days)</h2>
            <canvas id="pireps-chart" x-init="$nextTick(() => pirepsChart.render())"></canvas>
            <script>
                const pirepsChart = new Chart('pireps-chart', {
                    type: 'bar',
                    data: {
                        labels: <?= Json::encode(Page::$pageData->pireps_chart_labels) ?>,
                        datasets: [{
                            label: 'PIREPs',
                            backgroundColor: '<?= Page::$pageData->va_color ?>',
                            data: <?= Json::encode(Page::$pageData->pireps_chart_data) ?>,
                        }]
                    }
                });
            </script>
        </div>
        <div class="dark:bg-white/10 bg-gray-100 rounded p-3 shadow">
            <h2 class="text-2xl font-bold">New Pilots (<?= $days ?> days)</h2>
            <canvas id="pilots-chart" x-init="$nextTick(() => pilotsChart.render())"></canvas>
            <script>
                const pilotsChart = new Chart('pilots-chart', {
                    type: 'bar',
                    data: {
                        labels: <?= Json::encode(Page::$pageData->pilots_chart_labels) ?>,
                        datasets: [{
                            label: 'Pilot Applications',
                            backgroundColor: '<?= Page::$pageData->va_color ?>',
                            data: <?= Json::encode(Page::$pageData->pilots_chart_data) ?>,
                        }]
                    }
                });
            </script>
        </div>
        <div class="dark:bg-white/10 bg-gray-100 rounded p-3 shadow col-span-2">
            <h2 class="text-2xl font-bold mb-3">Top Pilots (7 days)</h2>
            <table class="table">
                <thead>
                    <th class="hidden md:table-cell">#</th>
                    <th>Pilot</th>
                    <th>Flight Time</th>
                </thead>
                <tbody>
                    <?php $i = 1; ?>
                    <?php foreach (Page::$pageData->leaderboard as $pilot) : ?>
                        <tr>
                            <td class="hidden md:table-cell">
                                <?= $i ?>
                            </td>
                            <td>
                                <?= $pilot->name ?>
                            </td>
                            <td>
                                <?= Time::secsToString($pilot->flighttime) ?>
                            </td>
                        </tr>
                        <?php $i++; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>