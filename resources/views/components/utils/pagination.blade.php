@props([
    'hasPages' => false,
    'currentPage' => null,
    'lastPage' => null,
    'onFirstPage' => false,
    'hasMorePages' => false,
    'getUrlRange' => null,
])

@if ($hasPages)
<nav aria-label="Page navigation">
    @php
        $edgeCount = 2;
        $windowSize = 3;

        $startPages = range(1, min($edgeCount, $lastPage));
        $middleStart = max($currentPage - $windowSize, 1);
        $middleEnd = min($currentPage + $windowSize, $lastPage);
        $middlePages = range($middleStart, $middleEnd);
        $endStart = max($lastPage - $edgeCount + 1, 1);
        $endPages = range($endStart, $lastPage);

        $pagesToShow = array_unique(array_merge($startPages, $middlePages, $endPages));
        sort($pagesToShow);
    @endphp
    <ul class="pagination pagination-rounded pagination-outline-secondary mb-0">
        {{-- Previous Page Link --}}
        <li class="page-item {{ $onFirstPage ? 'disabled' : '' }}">
            <a class="page-link"
                href="#"
                wire:click.prevent="gotoPage({{ $currentPage - 1 }})"
                aria-label="Previous"
                tabindex="{{ $onFirstPage ? '-1' : '0' }}">
                <i class="fi fi-rr-angle-double-left"></i>
            </a>
        </li>

        {{-- Pagination Elements --}}
        @php $previousPage = null; @endphp
        @foreach ($pagesToShow as $page)
            @if (!is_null($previousPage) && ($page - $previousPage) > 1)
                <li class="page-item disabled">
                    <span class="page-link">...</span>
                </li>
            @endif

            <li class="page-item {{ $page == $currentPage ? 'active' : '' }}">
                <a class="page-link"
                    href="#"
                    wire:click.prevent="gotoPage({{ $page }})">{{ $page }}</a>
            </li>

            @php $previousPage = $page; @endphp
        @endforeach

        {{-- Next Page Link --}}
        <li class="page-item {{ !$hasMorePages ? 'disabled' : '' }}">
            <a class="page-link"
                href="#"
                wire:click.prevent="gotoPage({{ $currentPage + 1 }})"
                aria-label="Next"
                tabindex="{{ !$hasMorePages ? '-1' : '0' }}">
                <i class="fi fi-rr-angle-double-right"></i>
            </a>
        </li>
    </ul>
</nav>
@endif