function eventstable(data) {
    if (!data) {
        return `<tr class="hover:bg-opacity-20">
            <td class="px-3 py-2 text-center" colspan="3">Loading...</td>
        </tr>`;
    }

    return data
        .map(
            (e) => `
                <tr class="hover:bg-black/20 cursor-pointer" onclick="window.location.href = '/events/${
                    e.id
                }'">
                    <td>
                        ${e.name}
                    </td><td class="hidden md:table-cell">
                        ${new Date(e.date + 'Z').toLocaleString()}
                    </td><td>
                        ${e.departureIcao}
                    </td>
                </tr>
            `
        )
        .join('');
}

function pirepstable(data) {
    if (!data) {
        return `<tr class="hover:bg-opacity-20">
            <td class="px-3 py-2 text-center" colspan="3">Loading...</td>
        </tr>`;
    }

    return data
        .slice(0, 5)
        .map((p) => {
            let pirepStatus = ['Pending', 'Approved', 'Denied'];
            return `
            <tr class="hover:bg-opacity-20">
                <td class="px-3 py-2">${p.departure}-${p.arrival}</td>
                <td class="px-3 py-2 hidden md:table-cell">${p.aircraft}</td>
                <td class="px-3 py-2">${pirepStatus[p.status]}</td>
            </tr>
        `;
        })
        .join('');
}

function pirepstats(data) {
    if (!data) {
        return '<li class="w-5/6 h-4 mb-1 bg-black/30 dark:bg-white/30 rounded animate-pulse"></li><li class="w-2/3 h-4 mb-1 bg-black/30 dark:bg-white/30 rounded animate-pulse"></li><li class="w-full h-4 mb-1 bg-black/30 dark:bg-white/30 rounded animate-pulse"></li>';
    }

    if (data.length < 1) {
        return '<li class="font-bold">No data yet!</li>';
    }

    let visits = {};
    let aircraft = {};
    for (let p of data) {
        if (!visits[p.arrival]) {
            visits[p.arrival] = 1;
        } else {
            visits[p.arrival]++;
        }

        if (!visits[p.departure]) {
            visits[p.departure] = 1;
        } else {
            visits[p.departure]++;
        }

        if (!aircraft[p.aircraft]) {
            aircraft[p.aircraft] = 1;
        } else {
            aircraft[p.aircraft]++;
        }
    }

    let topvisit = Object.entries(visits)
        .sort(([_, b], [__, d]) => b - d)
        .pop();
    let topaircraft = Object.entries(aircraft)
        .sort(([_, b], [__, d]) => b - d)
        .pop();
    let longestflight = data.sort((a, b) => a.flighttime - b.flighttime).pop();

    return `
        <li>
            <b>Favorite Airport:</b> ${topvisit[0]} (${topvisit[1]} visits)
        </li>
        <li>
            <b>Favorite Aircraft:</b> ${topaircraft[0]} (${
        topaircraft[1]
    } flights)
        </li>
        <li>
            <b>Longest Flight:</b> ${longestflight.departure}-${
        longestflight.arrival
    } (${Math.floor(longestflight.flighttime / 3600)}hrs ${Math.floor(
        (longestflight.flighttime / 60) % 60
    )}mins)
        </li>
    `;
}

function newsfeed(data) {
    if (!data) {
        return `
            <h3 class="text-2xl font-bold mb-3">News Feed</h3>
            <div class="rounded shadow-md w-full p-3 space-y-1 border border-gray-200 dark:border-transparent dark:bg-white/10 dark:text-white">
                <div class="w-3/4 h-6 bg-black/30 dark:bg-white/30 rounded animate-pulse mb-3"></div>
                <div class="grid grid-cols-6 gap-2">
                    <div class="col-span-4 inline-block h-4 bg-black/30 dark:bg-white/30 rounded animate-pulse"></div>
                    <div class="col-span-2 inline-block h-4 bg-black/30 dark:bg-white/30 rounded animate-pulse"></div>
                    <div class="col-span-1 inline-block h-4 bg-black/30 dark:bg-white/30 rounded animate-pulse"></div>
                    <div class="col-span-5 inline-block h-4 bg-black/30 dark:bg-white/30 rounded animate-pulse"></div>
                    <div class="col-span-3 inline-block h-4 bg-black/30 dark:bg-white/30 rounded animate-pulse"></div>
                    <div class="col-span-2 inline-block h-4 bg-black/30 dark:bg-white/30 rounded animate-pulse"></div>
                </div>
            </div>
        `;
    }

    return (
        '<h3 class="text-2xl font-bold mb-3">News Feed</h3>' +
        data
            .map(
                (n) => `
                    <div class="rounded shadow-md w-full p-3 border border-gray-200 dark:border-transparent dark:bg-white/10 dark:text-white">
                        <h4 class="text-xl font-semibold">${n.title}</h4>
                        <p class="mb-1">${n.content}</p>
                        <small class="inline-block">Posted by ${
                            n.author
                        } on ${new Date(
                    n.dateposted
                ).toLocaleDateString()}</small>
                    </div>
                `
            )
            .join('')
    );
}

function atctable(data) {
    if (!data) {
        return `<tr>
            <td class="px-3 py-2 text-center" colspan="2">Loading...</td>
        </tr>`;
    }

    const stationTypes = ['G', 'T', '', '', 'A', 'D', 'C', 'S', '', '', '', ''];

    data = data.sort((a, b) => {
        if (a.type < b.type) {
            return -1;
        } else if (a.type > b.type) {
            return 1;
        } else {
            return 0;
        }
    });

    let airports = {};
    for (const s of data) {
        if (!s.airportName) {
            s.airportName = 'N/A';
        }

        if (!airports[s.airportName]) {
            airports[s.airportName] = stationTypes[s.type];
        } else {
            airports[s.airportName] += stationTypes[s.type];
        }
    }

    return Object.entries(airports)
        .map(
            ([a, s]) => `
                <tr>
                    <td>
                        ${
                            a == 'N/A'
                                ? a
                                : `<a href="/airport/${encodeURIComponent(
                                      a
                                  )}" class="hover:underline">${a}</a>`
                        }
                    </td><td>
                        ${s}
                    </td>
                </tr>
            `
        )
        .join('');
}

function airportAtcTable(data, icao) {
    if (!data) {
        return `<tr>
            <td class="px-3 py-2 text-center" colspan="2">Loading...</td>
        </tr>`;
    }

    const stationTypes = [
        'Ground',
        'Tower',
        '',
        '',
        'Approach',
        'Departure',
        'Center',
        'ATIS',
        '',
        '',
        '',
        '',
    ];

    data = data
        .filter((x) => x.airportName == icao)
        .sort((a, b) => {
            if (a.type < b.type) {
                return -1;
            } else if (a.type > b.type) {
                return 1;
            } else {
                return 0;
            }
        });

    if (data.length < 1) {
        return `<tr>
            <td class="px-3 py-2 text-center" colspan="2">No Active ATC</td>
        </tr>`;
    }

    return data
        .map(
            (station) => `
                <tr>
                    <td>
                        ${stationTypes[station.type]}
                    </td>
                    <td>
                        ${station.username}
                    </td>
                </tr>
            `
        )
        .join('');
}

function inithome(data) {
    fetch('/api.php/events')
        .then((r) => r.json())
        .then((r) => (data.events = r.result));
    fetch('/api.php/pireps')
        .then((r) => r.json())
        .then((r) => (data.pireps = r.result));
    fetch('/api.php/news')
        .then((r) => r.json())
        .then((r) => (data.news = r.result));
    fetch('/api.php/atc')
        .then((r) => r.json())
        .then((r) => (data.atc = r.result));
}

function initevents(data) {
    fetch('/api.php/events')
        .then((r) => r.json())
        .then((r) => (data.events = r.result));
}

function initairport(data, icao) {
    fetch('/api.php/atc')
        .then((r) => r.json())
        .then((r) => (data.atc = r.result));
    fetch(`/api.php/airport/${encodeURIComponent(icao)}/atis`)
        .then((r) => r.json())
        .then((r) => (data.atis = r.result));
}

function toggleEventSignup(id) {
    fetch(`/api.php/events/${encodeURIComponent(id)}`, {
        method: 'PUT',
    }).then(() => location.reload());
    alert('Updating, please wait.');
}

function initbadges(data) {
    fetch('/api.php/menu/badges')
        .then((r) => r.json())
        .then((r) => {
            for (const [k, v] of Object.entries(r.result)) {
                data[k] = v;
            }
        });
}

function anyCategoryBadge(id, badges) {
    const badgeIds = JSON.parse(
        document.getElementById(`badges_${id}`).innerHTML
    );
    return Object.keys(badges).some(
        (k) => badgeIds.includes(k) && badges[k] != 0
    );
}

function resizeTextarea(el) {
    el.style.height = 'auto';
    el.style.height = el.scrollHeight + 5 + 'px';
}

function formatFlightTime() {
    const val = typeof this == 'string' ? parseInt(this) : this;
    const hours = Math.floor(val / 3600);
    const minutes = (val % 3600) / 60;
    return `${hours.toString().padStart(2, '0')}:${minutes
        .toString()
        .padStart(2, '0')}`;
}
Number.prototype.formatFlightTime = formatFlightTime;
String.prototype.formatFlightTime = formatFlightTime;

async function repairSite() {
    await fetch('/api.php/repair');
}

async function migrateConfig() {
    await fetch('/api.php/config_migrate');
}

function updaterOverlay(enabled) {
    if (enabled) {
        const overlay = document.createElement('div');
        overlay.className =
            'fixed top-0 left-0 w-screen h-screen bg-black/70 z-50 flex text-white';
        overlay.id = 'updater-overlay';
        overlay.innerHTML = `
            <div class="m-auto text-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 animate-spin-backwards mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                <h1 class="text-4xl font-bold mb-2">Updating...</h1>
                <p>Please wait while the site is updated</p>
            </div>
        `;
        document.getElementById('root')?.appendChild(overlay);
    } else {
        document.getElementById('updater-overlay')?.remove();
    }
}

async function updateSite(el) {
    updaterOverlay(true);
    const req = await fetch('/updater.php');
    const res = await req.text();
    el.innerHTML = res;
    updaterOverlay(false);
}
function generateId() {
    return this.toLowerCase().replace(/\s/g, '-');
}
String.prototype.generateId = generateId;

async function handleComment(e, val, pirep, setComments, setValue) {
    e.preventDefault();
    if (!val) return;

    setValue('');

    const data = new FormData();
    data.append('content', val);
    const req = await fetch(
        `/api.php/pireps/${encodeURIComponent(pirep)}/comments`,
        {
            method: 'POST',
            body: data,
        }
    );
    if (!req.ok) {
        alert('Failed to add comment');
        return;
    }

    const req2 = await fetch(
        `/api.php/pireps/${encodeURIComponent(pirep)}/comments`
    );
    const res = await req2.json();
    setComments(res.result);
}

async function fetchLiveries(aircraftid) {
    const req = await fetch(
        `/api.php/liveries?aircraftid=${encodeURIComponent(aircraftid)}`
    );
    const res = await req.json();
    return res.result;
}

function updateDataTable(allEntries, data) {
    let current = allEntries;
    if (data.search) {
        current = current.filter((x) => {
            return Object.values(x).some((v) =>
                v.toString().toLowerCase().includes(data.search.toLowerCase())
            );
        });
    }
    if (data.orderBy) {
        current = current.sort((a, b) => {
            const x = data.orderBy(a);
            const y = data.orderBy(b);
            if (x < y) return data.order === 'asc' ? -1 : 1;
            if (x > y) return data.order === 'asc' ? 1 : -1;
            return 0;
        });
    }

    data.current = [...current];
}

function dataTableOrder(fn, name, tableData) {
    tableData.orderBy = fn;
    tableData.order =
        tableData.order == 'asc' && tableData.orderByName == name
            ? 'desc'
            : 'asc';
    tableData.orderByName = name;
    updateDataTable(allEntries, tableData);
}

document.addEventListener('alpine:initializing', () => {
    Alpine.directive(
        'html',
        (el, { expression }, { evaluateLater, effect }) => {
            let getHtml = evaluateLater(expression);

            effect(() => {
                getHtml((html) => {
                    el.innerHTML = html;
                });
            });
        }
    );
});
