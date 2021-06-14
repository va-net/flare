function eventstable(data) {
    if (!data) {
        return `<tr class="border-b-2 border-black dark:border-white hover:bg-black hover:bg-opacity-20">
            <td class="px-3 py-2 text-center" colspan="3">Loading...</td>
        </tr>`;
    }

    return data.map(
        (e) => `
        <tr class="border-b-2 border-black dark:border-white hover:bg-black hover:bg-opacity-20 cursor-pointer" onclick="window.location.href = '/events/${
            e.id
        }'">
            <td class="px-3 py-2">
                ${e.name}
            </td><td class="px-3 py-2 hidden md:table-cell">
                ${new Date(e.date + 'Z').toLocaleString()}
            </td><td class="px-3 py-2">
                ${e.departureIcao}
            </td>
        </tr>
    `
    );
}

function pirepstable(data) {
    if (!data) {
        return `<tr class="border-b-2 border-black dark:border-white hover:bg-black hover:bg-opacity-20">
            <td class="px-3 py-2 text-center" colspan="3">Loading...</td>
        </tr>`;
    }

    return data.slice(0, 5).map((p) => {
        let pirepStatus = ['Pending', 'Approved', 'Denied'];
        return `
            <tr class="border-b-2 border-black dark:border-white hover:bg-black hover:bg-opacity-20">
                <td class="px-3 py-2">${p.departure}-${p.arrival}</td>
                <td class="px-3 py-2 hidden md:table-cell">${p.aircraft}</td>
                <td class="px-3 py-2">${pirepStatus[p.status]}</td>
            </tr>
        `;
    });
}

function pirepstats(data) {
    if (!data) {
        return '<li>Loading...</li>';
    }

    if (data.length < 1) {
        return '<li>No data yet!</li>';
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
        return '<p>Loading...</p>';
    }

    return (
        '<h3 class="text-2xl font-bold mb-3">News Feed</h3>' +
        data
            .map(
                (n) => `
                    <div class="rounded shadow-md w-full p-3 border border-gray-200 dark:border-transparent dark:bg-white dark:bg-opacity-10 dark:text-white">
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
