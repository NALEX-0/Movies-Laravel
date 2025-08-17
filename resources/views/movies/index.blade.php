<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('My Watched Movies') }}
        </h2>
    </x-slot>

    <div class="py-4">
        <div class=" mx-auto sm:px-6 lg:px-8">

            <div
                x-data="{ show: false, message: '' }"
                x-init="
                    @if($errors->has('tmdb_id'))
                    show = true;
                    message = '{{ $errors->first('tmdb_id') }}';
                    setTimeout(() => show = false, 4000);
                    @endif
                "
                x-show="show"
                x-transition
                class="fixed top-6 right-6 z-50 bg-red-600 text-white text-sm font-medium px-4 py-2 rounded shadow"
                style="display: none;"
                >
                <span x-text="message"></span>
            </div>

            {{-- Add Movie Button --}}
            <div class="mb-4 flex justify-end">
                <button
                    type="button"
                    data-modal-target="crud-modal"
                    data-modal-toggle="crud-modal"
                        class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300
                        font-medium rounded-lg text-sm px-5 py-2.5 text-center
                        dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                    Add Movie
                </button>
            </div>

            {{-- Movies Table --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm border border-gray-200 dark:border-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
                            <tr class="text-gray-700 dark:text-gray-300">
                                <th scope="col" class="px-6 py-4">Poster</th>
                                <th scope="col" class="px-6 py-4">Title</th>
                                <th scope="col" class="px-6 py-4">Year</th>
                                <th scope="col" class="px-6 py-4">Date Watched</th>
                                <th scope="col" class="px-6 py-4">Rating</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-gray-900 dark:text-gray-100">
                            @forelse ($movies as $movie)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-900 transition">
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <div class="flex justify-center items-center">
                                            @if($movie->poster_url)
                                                <img src="{{ $movie->poster_url }}" width="50" height="70" class="rounded-lg" alt="{{ $movie->title }}">
                                            @else
                                                <span class="text-gray-400 dark:text-gray-500">—</span>
                                            @endif
                                        </div>
                                    </td>

                                    <td class="whitespace-nowrap px-6 py-4 font-medium">{{ $movie->title }}</td>
                                    <td class="whitespace-nowrap px-6 py-4">{{ $movie->year ?? '—' }}</td>
                                    <td class="whitespace-nowrap px-6 py-4">{{ $movie->pivot->date_watched ?? '—' }}</td>
                                    <td class="whitespace-nowrap px-6 py-4">{{ $movie->pivot->rating ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                        There are no movies in your list. Click “Add Movie” to add one.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $movies->links() }}
                </div>
            </div>
        </div>
    </div>

    {{-- Add Movie Modal --}}
    <div id="crud-modal"
        tabindex="-1"
        aria-hidden="true"
        class="hidden fixed inset-0 z-50 bg-black/40 flex items-center justify-center">
        <div class="relative px-4">
            <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden flex flex-col">
            <div class="flex items-center justify-between p-4 md:p-5 border-b border-gray-200 dark:border-gray-600">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Add Movie</h3>
                <button type="button"
                        class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 inline-flex justify-center items-center
                            dark:hover:bg-gray-600 dark:hover:text-white"
                        data-modal-hide="crud-modal">
                <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                </svg>
                <span class="sr-only">Close modal</span>
                </button>
            </div>

            <form class="flex-1 px-6 py-4 overflow-hidden flex flex-col gap-4" method="POST" action="{{ route('movies.add') }}">
                @csrf
                <input type="hidden" name="tmdb_id" id="tmdb_id">
                <input type="hidden" name="title" id="movie_title">
                <input type="hidden" name="year" id="movie_year">
                <input type="hidden" name="poster_url" id="poster_url">


                <div>
                <label for="tmdbQuery" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Search TMDB</label>
                <input
                    type="text" id="tmdbQuery" autocomplete="off" placeholder="Type at least 2 letters…"
                    class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg
                        focus:ring-blue-500 focus:border-blue-500 block p-2.5
                        dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
                </div>

                <div id="tmdbResults"
                    class="hidden max-h-32 overflow-y-auto overscroll-contain
                        rounded-md border border-gray-200 dark:border-gray-700
                        bg-white dark:bg-gray-900 shadow-sm text-xs">

                </div>

                <p id="selectedMovieInfo" class="text-sm text-gray-600 dark:text-gray-400 hidden"></p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="date_watched" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Date watched</label>
                    <input type="date" name="date_watched" id="date_watched"
                        class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg
                            focus:ring-blue-500 focus:border-blue-500 block p-2.5
                            dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
                </div>
                <div>
                    <label for="rating" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Rating (0–10)</label>
                    <input type="number" step="0.1" min="0" max="10" name="rating" id="rating" placeholder="e.g., 8.5"
                        class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg
                            focus:ring-blue-500 focus:border-blue-500 block p-2.5
                            dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white">
                </div>
                </div>

                <div class="mt-auto flex justify-end gap-3 pt-2 border-t border-gray-200 dark:border-gray-600">
                <button type="button" data-modal-hide="crud-modal"
                        class="text-white bg-gray-800 hover:bg-gray-900 focus:outline-none focus:ring-4 focus:ring-gray-300
                            font-medium rounded-lg text-sm px-5 py-2.5
                            dark:bg-gray-800 dark:hover:bg-gray-700 dark:focus:ring-gray-700 dark:border-gray-700">
                        Cancel
                </button>
                <button type="submit" id="saveMovieBtn" disabled
                        class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300
                            font-medium rounded-lg text-sm px-5 py-2.5 disabled:opacity-50
                            dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                        Save
                </button>
                </div>
            </form>
            </div>
        </div>
    </div>



    @push('scripts')
        <script type="module">

            // Search bar
            document.getElementById('searchInput').addEventListener('keyup', function () {
            clearTimeout(timeout);
            const search = this.value;

            timeout = setTimeout(() => {
                fetch(`/movies?search=${encodeURIComponent(search)}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newTable = doc.getElementById('tableWrapper');
                    document.getElementById('tableWrapper').innerHTML = newTable.innerHTML;
                });
            }, 300); // Debounce: 300ms
        });

            // Modal
            const searchUrl = "{{ route('movies.search') }}";

            const qInput    = document.getElementById('tmdbQuery');
            const box       = document.getElementById('tmdbResults');
            const infoLine  = document.getElementById('selectedMovieInfo');
            const saveBtn   = document.getElementById('saveMovieBtn');

            const hidTmdb   = document.getElementById('tmdb_id');
            const hidTitle  = document.getElementById('movie_title');
            const hidYear   = document.getElementById('movie_year');
            const hidPoster = document.getElementById('poster_url');

            function debounce(fn, ms=300) { let t; return (...a) => { clearTimeout(t); t = setTimeout(() => fn(...a), ms); }; }
            function showBox(html) { box.innerHTML = html; box.classList.remove('hidden'); }
            function hideBox() { box.classList.add('hidden'); }
            function showSpinner() { showBox('<div class="px-2 py-1.5 text-xs text-gray-500 dark:text-gray-400">Searching…</div>'); }
            function showError(msg, code) { showBox(`<div class="px-2 py-1.5 text-xs text-red-600 dark:text-red-400">${msg}${code?' (HTTP '+code+')':''}</div>`); }

            function renderList(items) {
                if (!Array.isArray(items) || !items.length) {
                    showBox('<div class="px-2 py-1.5 text-xs text-gray-500 dark:text-gray-400">No results</div>');
                    return;
                }
                const limited = items.slice(0, 40);
                const rows = limited.map(it => `
                    <button type="button"
                    data-tmdb="${it.tmdb_id ?? ''}"
                    data-title="${(it.title || '').replace(/"/g,'&quot;')}"
                    data-year="${it.year ?? ''}"
                    data-poster="${it.poster_full ?? ''}"
                    class="w-full px-2 py-1.5 text-left text-xs leading-tight
                        flex items-center justify-between gap-2
                        hover:bg-gray-50 dark:hover:bg-gray-800
                        text-gray-900 dark:text-gray-100">
                    <span class="truncate max-w-[80%]"> ${it.title || ''} </span>
                    <span class="shrink-0 text-[10px] text-gray-500 dark:text-gray-400"> ${it.year ?? '—'} </span>
                    </button>
                `).join('');
                showBox(rows);

                box.querySelectorAll('button[data-tmdb]').forEach(btn => {
                    btn.addEventListener('click', () => {
                        const tmdb   = btn.dataset.tmdb || '';
                        const title  = btn.dataset.title || '';
                        const year   = btn.dataset.year || '';
                        const poster = btn.dataset.poster || '';

                        hidTmdb.value   = tmdb;
                        hidTitle.value  = title;
                        hidYear.value   = year;
                        hidPoster.value = poster;

                        qInput.value = title;
                        infoLine.textContent = `Selected: ${title}${year ? ' ('+year+')' : ''}`;
                        infoLine.classList.remove('hidden');

                        if (saveBtn) { saveBtn.disabled = !tmdb; }
                        hideBox();
                    });
                });
            }

            const runSearch = debounce(async (term) => {
                const query = (term || '').trim();
                if (query.length < 2) {
                    hideBox();
                    return;
                }
                try {
                    showSpinner();
                    const res = await fetch(`${searchUrl}?q=${encodeURIComponent(query)}`, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                    });
                    const txt = await res.text();
                    if (!res.ok) {
                        showError(txt || 'Request failed', res.status);
                        return;
                    }
                    let data;
                    try {
                        data = JSON.parse(txt);
                    } catch {
                        showError('Invalid JSON');
                        return;
                    }
                    if (!Array.isArray(data.results)) {
                        showError('Unexpected response');
                        return;
                    }
                    renderList(data.results);
                } catch(e) {
                    showError('Network error');
                    console.error(e);
                }
            }, 300);

            if (qInput) {
                qInput.addEventListener('input', e => runSearch(e.target.value));
                qInput.addEventListener('focus', () => {
                    if (qInput.value.trim().length >= 2 && box.innerHTML.trim()) {
                        box.classList.remove('hidden');
                    }
                });
            }
            document.addEventListener('click', (e) => { if (!box.contains(e.target) && e.target !== qInput) hideBox(); });
            document.addEventListener('keydown', (e) => { if (e.key === 'Escape') hideBox(); });
        </script>
    @endpush

</x-app-layout>

