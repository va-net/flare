<?php
Page::setTitle('My PIREPs - ' . Page::$pageData->va_name);
require_once __DIR__ . '/../includes/header.php';
?>
<div id="content" class="m-5 text-black dark:text-white">
    <h1 class="text-3xl font-bold">My PIREPs</h1>
    <p class="mb-3">Showing your 30 most recent PIREPs</p>
    <div class="inline-block w-full align-middle">
        <div class="table-wrapper">
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col" class="hidden px-6 py-3 text-xs font-medium tracking-wider text-left uppercase lg:table-cell">
                            Date
                        </th>
                        <th scope="col" class="hidden px-6 py-3 text-xs font-medium tracking-wider text-left uppercase lg:table-cell">
                            Flight #
                        </th>
                        <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left uppercase">
                            Route
                        </th>
                        <th scope="col" class="hidden px-6 py-3 text-xs font-medium tracking-wider text-left uppercase lg:table-cell">
                            Aircraft
                        </th>
                        <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left uppercase">
                            Status
                        </th>
                        <th scope="col" class="relative px-6 py-3">
                            <span class="sr-only">Edit</span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (Page::$pageData->pireps as $pirep) : ?>
                        <tr>
                            <td class="hidden lg:table-cell">
                                <?= date_format(date_create($pirep['date']), 'Y-m-d') ?>
                            </td>
                            <td class="hidden lg:table-cell">
                                <?= $pirep['fnum'] ?>
                            </td>
                            <td>
                                <?= $pirep['departure'] . '-' . $pirep['arrival'] ?>
                            </td>
                            <td class="hidden lg:table-cell">
                                <?= $pirep['aircraft'] ?>
                            </td>
                            <td>
                                <?php
                                $statuses = [
                                    'Approved' => 'bg-green-100 text-green-800 dark:bg-green-300 dark:text-green-900',
                                    'Denied' => 'bg-red-200 text-red-900',
                                    'Pending' => 'bg-yellow-200 text-yellow-800'
                                ];
                                ?>
                                <span class="inline-flex px-2 text-xs font-semibold leading-5 <?= $statuses[$pirep['status']] ?> rounded-full">
                                    <?= $pirep['status'] ?>
                                </span>
                            </td>
                            <td class="px-6 py-3 text-sm font-medium text-right whitespace-nowrap">
                                <a href="/pireps/<?= $pirep['id'] ?>" class="inline-flex p-2 rounded-md shadow-md bg-primary text-primary-text focus:outline-none focus:ring-2 focus:ring-transparent focus:ring-offset-1 focus:ring-offset-black dark:focus:ring-offset-white">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>