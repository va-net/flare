<?php
Page::setTitle('Home - ' . Page::$pageData->va_name);
require_once __DIR__ . '/../includes/header.php';
?>
<div id="content" x-data="{ data: { events: null, pireps: null, news: null } }" x-init="inithome(data)">
    <div class="w-full p-5 shadow-lg bg-primary text-primary-text h-36 pt-7">
        <h2 class="text-4xl font-bold text-center">
            Welcome, <?= escape(Page::$pageData->user->data()->name) ?>
        </h2>
    </div>
    <div class="flex-row w-full gap-5 px-5 space-y-3 md:flex md:space-y-0 -mt-14 mb-7">
        <div class="flex items-center flex-auto w-full p-4 bg-white border rounded shadow-lg min-h-20">
            <div class="flex w-12 h-12 mr-3 text-white bg-red-600 rounded-full">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 m-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
            </div>
            <div>
                <h3 class="text-lg font-bold">PIREPs Filed</h3>
                <h5 class="font-semibold" x-text="data.pireps?.length || '...'"></h5>
            </div>
        </div>
        <div class="flex items-center flex-auto w-full p-4 bg-white border rounded shadow-lg min-h-20">
            <div class="flex w-12 h-12 mr-3 text-white bg-blue-600 rounded-full">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 m-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                </svg>
            </div>
            <div>
                <h3 class="text-lg font-bold">Flight Time</h3>
                <h5 class="font-semibold"><?= escape(Time::secsToString(Page::$pageData->user->getFlightTime())) ?></h5>
            </div>
        </div>
        <div class="flex items-center flex-auto w-full p-4 bg-white border rounded shadow-lg min-h-20">
            <div class="flex w-12 h-12 mr-3 text-white bg-green-500 rounded-full">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 m-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
                </svg>
            </div>
            <div>
                <h3 class="text-lg font-bold">Rank</h3>
                <h5 class="font-semibold">
                    <?= escape(Page::$pageData->user->rank()) ?>
                </h5>
            </div>
        </div>
        <div class="flex items-center flex-auto w-full p-4 bg-white border rounded shadow-lg min-h-20">
            <div class="flex w-12 h-12 mr-3 text-white bg-yellow-500 rounded-full">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 m-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
            <div>
                <h3 class="text-lg font-bold">Last PIREP</h3>
                <?php $pirep = Page::$pageData->user->recentPireps(null, 1); ?>
                <h5 class="font-semibold" x-html="data.pireps ? (data.pireps[0] ? data.pireps[0].date : 'No PIREPs') : '...'"></h5>
            </div>
        </div>
    </div>
    <!-- Information Grid -->
    <div class="w-full px-5 space-y-4 md:grid md:grid-cols-12 md:gap-4 md:space-y-0">
        <!-- Recent PIREPs (Silver) or Upcoming Events (Gold) -->
        <div class="col-span-7 p-4 border border-gray-200 rounded-lg shadow-lg dark:border-transparent dark:bg-white dark:bg-opacity-5 dark:text-white">
            <?php if (Page::$pageData->is_gold) : ?>
                <h3 class="mb-3 text-2xl font-bold">
                    Upcoming Events
                </h3>
                <div class="mb-2 table-wrapper">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th class="hidden md:table-cell">
                                    Date
                                </th>
                                <th>Airport</th>
                            </tr>
                        </thead>
                        <tbody x-html="eventstable(data.events)"></tbody>
                    </table>
                </div>
                <small class="block mb-3 text-center">
                    Click on any event to view details.
                </small>
            <?php else : ?>
                <h3 class="mb-3 text-2xl font-bold">
                    Recent PIREPs
                </h3>
                <div class="table-wrapper">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Route</th>
                                <th class="hidden md:table-cell">
                                    Aircraft
                                </th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody x-html="pirepstable(data.pireps)"></tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
        <!-- Route Search -->
        <div class="col-span-5 p-4 border border-gray-200 rounded-lg shadow-lg dark:border-transparent dark:bg-white dark:bg-opacity-5 dark:text-white">
            <h3 class="mb-3 text-2xl font-bold">
                Find Flights
            </h3>
            <form action="/routes/search" method="get">
                <label class="block mb-2">
                    <span class="text-black dark:text-white">Departure ICAO</span>
                    <input type="text" class="form-control" name="dep" placeholder="YMML" />
                </label>
                <label class="block mb-3">
                    <span class="text-black dark:text-white">Arrival ICAO</span>
                    <input type="text" class="form-control" name="arr" placeholder="YSSY" />
                </label>
                <button type="submit" class="px-3 py-2 rounded-md shadow-md bg-primary text-primary-text focus:outline-none focus:ring-2 focus:ring-transparent focus:ring-offset-1 focus:ring-offset-black dark:focus:ring-offset-white">
                    Search
                </button>
            </form>
        </div>
        <!-- Statistics -->
        <div class="col-span-5 p-4 border border-gray-200 rounded-lg shadow-lg dark:border-transparent dark:bg-white dark:bg-opacity-5 dark:text-white">
            <h3 class="mb-3 text-2xl font-bold">
                Your Statistics
            </h3>
            <ul x-html="pirepstats(data.pireps)"></ul>
        </div>
        <!-- News Feed -->
        <div class="col-span-7 p-4 space-y-2 border border-gray-200 rounded-lg shadow-lg dark:border-transparent dark:bg-white dark:bg-opacity-5 dark:text-white" x-html="newsfeed(data.news)">
            <h3 class="mb-3 text-2xl font-bold">News Feed</h3>
            <p>Loading...</p>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>