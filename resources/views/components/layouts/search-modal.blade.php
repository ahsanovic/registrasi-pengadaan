<div class="modal fade" id="searchResultsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
        <div class="modal-header py-1 px-3">
        <form class="d-flex align-items-center position-relative w-100" id="rkSearchForm" action="#">
            <button type="button" class="btn btn-sm border-0 position-absolute start-0 p-0 text-sm ">
            <i class="fi fi-rr-search"></i>
            </button>
            <input type="text" class="form-control form-control-lg ps-4 border-0 shadow-none" id="rkSearchInput" placeholder="Cari rencana kegiatan...">
        </form>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body pb-2" style="height: 300px;" data-simplebar>
        <div id="rkRecentlyResults">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-uppercase text-2xs fw-semibold text-muted">Recently Searched:</span>
                <button type="button" class="btn btn-link btn-sm p-0 text-danger" id="clearSearchHistoryBtn">
                    Hapus Riwayat
                </button>
            </div>
            <ul class="list-inline search-list" id="rkRecentlySearchList">
                <li class="text-muted text-sm text-center" id="rkRecentlySearchEmpty">Belum ada riwayat pencarian rencana kegiatan.</li>
            </ul>
        </div>
        <div id="rkSearchContainer" class="mt-3 d-none">
            <span class="text-uppercase text-2xs fw-semibold text-muted d-block mb-2">Hasil Pencarian:</span>
            <ul class="list-inline search-list" id="rkSearchResultsList"></ul>
            <div class="text-muted text-sm d-none" id="rkSearchNoResults">Data rencana kegiatan tidak ditemukan.</div>
            <div class="text-muted text-sm d-none" id="rkSearchLoading">Mencari...</div>
        </div>
        </div>
    </div>
    </div>
</div>

<script>
    (() => {
        const STORAGE_KEY = 'recent_rencana_kegiatan_searches';
        const MAX_HISTORY = 6;

        const escapeHtml = (text) => {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        };

        const getHistory = () => {
            try {
                return JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]');
            } catch (error) {
                return [];
            }
        };

        const saveHistory = (keyword) => {
            const trimmed = keyword.trim();
            if (trimmed.length < 2) {
                return;
            }

            const history = getHistory().filter(item => item.toLowerCase() !== trimmed.toLowerCase());
            history.unshift(trimmed);
            localStorage.setItem(STORAGE_KEY, JSON.stringify(history.slice(0, MAX_HISTORY)));
        };

        const clearHistory = () => {
            localStorage.removeItem(STORAGE_KEY);
        };

        const renderHistory = (recentlySearchList) => {
            const history = getHistory();

            if (!history.length) {
                recentlySearchList.innerHTML = '<li class="text-muted text-sm text-center" id="rkRecentlySearchEmpty">Belum ada riwayat pencarian rencana kegiatan.</li>';
                return;
            }

            recentlySearchList.innerHTML = history
                .map(item => `
                    <li>
                        <a class="search-item js-history-item" href="#" data-keyword="${escapeHtml(item)}">
                            <i class="fi fi-rr-time-past"></i> ${escapeHtml(item)}
                        </a>
                    </li>
                `)
                .join('');
        };

        const clearResults = (searchResultsList, searchNoResults, searchLoading, searchContainer) => {
            searchResultsList.innerHTML = '';
            searchNoResults.classList.add('d-none');
            searchLoading.classList.add('d-none');
            searchContainer.classList.add('d-none');
        };

        const renderResults = (items, searchResultsList, searchNoResults) => {
            if (!items.length) {
                searchResultsList.innerHTML = '';
                searchNoResults.classList.remove('d-none');
                return;
            }

            searchNoResults.classList.add('d-none');
            searchResultsList.innerHTML = items
                .map(item => `
                    <li>
                        <a class="search-item js-result-item" href="${item.url}" data-keyword="${escapeHtml(item.label)}">
                            <i class="fi fi-rr-search-alt"></i> ${escapeHtml(item.label)}
                        </a>
                    </li>
                `)
                .join('');
        };

        const initRencanaKegiatanSearch = () => {
            const modalElement = document.getElementById('searchResultsModal');
            const modalInput = document.getElementById('rkSearchInput');
            const headerInput = document.getElementById('headerSearchInput');
            const recentlySearchList = document.getElementById('rkRecentlySearchList');
            const searchContainer = document.getElementById('rkSearchContainer');
            const searchResultsList = document.getElementById('rkSearchResultsList');
            const searchNoResults = document.getElementById('rkSearchNoResults');
            const searchLoading = document.getElementById('rkSearchLoading');
            const searchForm = document.getElementById('rkSearchForm');
            const clearSearchHistoryBtn = document.getElementById('clearSearchHistoryBtn');

            if (!modalElement || !modalInput || !searchResultsList || !recentlySearchList || !searchForm) {
                return;
            }

            if (modalElement.dataset.rkSearchBound === '1') {
                renderHistory(recentlySearchList);
                return;
            }
            modalElement.dataset.rkSearchBound = '1';

            let debounceTimer;

            const searchRencanaKegiatan = async (keyword) => {
                const trimmed = keyword.trim();

                if (trimmed.length < 2) {
                    clearResults(searchResultsList, searchNoResults, searchLoading, searchContainer);
                    return;
                }

                searchContainer.classList.remove('d-none');
                searchLoading.classList.remove('d-none');
                searchNoResults.classList.add('d-none');

                try {
                    const response = await fetch(`{{ route('search.rencana-kegiatan') }}?q=${encodeURIComponent(trimmed)}`);
                    const result = await response.json();
                    renderResults(Array.isArray(result.data) ? result.data : [], searchResultsList, searchNoResults);
                } catch (error) {
                    searchResultsList.innerHTML = '';
                    searchNoResults.classList.remove('d-none');
                    searchNoResults.textContent = 'Terjadi kesalahan saat mengambil data.';
                } finally {
                    searchLoading.classList.add('d-none');
                }
            };

            const runSearch = (keyword) => {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    searchRencanaKegiatan(keyword);
                }, 250);
            };

            searchForm.addEventListener('submit', (event) => {
                event.preventDefault();
                runSearch(modalInput.value);
            });

            modalInput.addEventListener('input', (event) => {
                runSearch(event.target.value);
            });

            if (headerInput) {
                const openModalAndSearch = (value) => {
                    const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
                    modal.show();
                    modalInput.value = value;
                    runSearch(value);
                };

                headerInput.addEventListener('input', (event) => {
                    const value = event.target.value || '';
                    if (value.trim().length >= 2) {
                        openModalAndSearch(value);
                    }
                });
            }

            modalElement.addEventListener('shown.bs.modal', () => {
                const sourceKeyword = (headerInput?.value || '').trim();
                modalInput.value = sourceKeyword;
                modalInput.focus();
                renderHistory(recentlySearchList);

                if (sourceKeyword.length >= 2) {
                    runSearch(sourceKeyword);
                } else {
                    clearResults(searchResultsList, searchNoResults, searchLoading, searchContainer);
                }
            });

            modalElement.addEventListener('click', (event) => {
                const historyLink = event.target.closest('.js-history-item');
                if (historyLink) {
                    event.preventDefault();
                    const keyword = historyLink.dataset.keyword || '';
                    modalInput.value = keyword;
                    runSearch(keyword);
                    return;
                }

                const resultLink = event.target.closest('.js-result-item');
                if (resultLink) {
                    const keyword = resultLink.dataset.keyword || '';
                    saveHistory(keyword);
                    renderHistory(recentlySearchList);
                }
            });

            clearSearchHistoryBtn?.addEventListener('click', () => {
                clearHistory();
                renderHistory(recentlySearchList);
            });

            modalInput.addEventListener('keydown', (event) => {
                if (event.key === 'Enter') {
                    const firstItem = searchResultsList.querySelector('.js-result-item');
                    if (firstItem) {
                        saveHistory(firstItem.dataset.keyword || modalInput.value);
                        window.location.href = firstItem.getAttribute('href');
                    }
                }
            });

            renderHistory(recentlySearchList);
        };

        initRencanaKegiatanSearch();
        document.addEventListener('livewire:navigated', initRencanaKegiatanSearch);
    })();
</script>