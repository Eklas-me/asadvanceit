@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation"
        style="display: flex; justify-content: center; align-items: center; gap: 8px;">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <span
                style="display: inline-flex; align-items: center; justify-content: center; padding: 8px 12px; border-radius: 8px; background: var(--card-bg); border: 1px solid var(--border-color); color: var(--text-muted); cursor: not-allowed; font-size: 14px;">
                <i class="fas fa-chevron-left" style="font-size: 12px;"></i>
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev"
                style="display: inline-flex; align-items: center; justify-content: center; padding: 8px 12px; border-radius: 8px; background: var(--card-bg); border: 1px solid var(--border-color); color: var(--text-primary); text-decoration: none; font-size: 14px; transition: all 0.2s;">
                <i class="fas fa-chevron-left" style="font-size: 12px;"></i>
            </a>
        @endif

        {{-- Pagination Elements --}}
        @foreach ($elements as $element)
            {{-- "Three Dots" Separator --}}
            @if (is_string($element))
                <span
                    style="display: inline-flex; align-items: center; justify-content: center; padding: 8px 12px; color: var(--text-muted); font-size: 14px;">{{ $element }}</span>
            @endif

            {{-- Array Of Links --}}
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span aria-current="page"
                            style="display: inline-flex; align-items: center; justify-content: center; min-width: 36px; height: 36px; padding: 8px 12px; border-radius: 8px; background: var(--accent-blue); color: white; font-weight: 600; font-size: 14px;">
                            {{ $page }}
                        </span>
                    @else
                        <a href="{{ $url }}"
                            style="display: inline-flex; align-items: center; justify-content: center; min-width: 36px; height: 36px; padding: 8px 12px; border-radius: 8px; background: var(--card-bg); border: 1px solid var(--border-color); color: var(--text-primary); text-decoration: none; font-size: 14px; transition: all 0.2s;">
                            {{ $page }}
                        </a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next"
                style="display: inline-flex; align-items: center; justify-content: center; padding: 8px 12px; border-radius: 8px; background: var(--card-bg); border: 1px solid var(--border-color); color: var(--text-primary); text-decoration: none; font-size: 14px; transition: all 0.2s;">
                <i class="fas fa-chevron-right" style="font-size: 12px;"></i>
            </a>
        @else
            <span
                style="display: inline-flex; align-items: center; justify-content: center; padding: 8px 12px; border-radius: 8px; background: var(--card-bg); border: 1px solid var(--border-color); color: var(--text-muted); cursor: not-allowed; font-size: 14px;">
                <i class="fas fa-chevron-right" style="font-size: 12px;"></i>
            </span>
        @endif
    </nav>

    <style>
        /* Hover effects for pagination links */
        nav[aria-label="Pagination Navigation"] a:hover {
            background: var(--accent-blue) !important;
            color: white !important;
            border-color: var(--accent-blue) !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }
    </style>
@endif