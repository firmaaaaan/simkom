@if ($paginator->hasPages())
    <nav>
        <ul class="custom-pagination">
            @if ($paginator->onFirstPage())
                <li class="custom-pagination-item disabled" aria-disabled="true" aria-label="{{ __('pagination.previous') }}">
                    <span class="custom-pagination-link">&laquo;</span>
                </li>
            @else
                <li class="custom-pagination-item">
                    <a class="custom-pagination-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="{{ __('pagination.previous') }}">
                        <span class="custom-pagination-arrow">&laquo;</span>
                    </a>
                </li>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <li class="custom-pagination-item disabled" aria-disabled="true">
                        <span class="custom-pagination-link">{{ $element }}</span>
                    </li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="custom-pagination-item active" aria-current="page">
                                <span class="custom-pagination-link">{{ $page }}</span>
                            </li>
                        @else
                            <li class="custom-pagination-item">
                                <a class="custom-pagination-link" href="{{ $url }}">{{ $page }}</a>
                            </li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <li class="custom-pagination-item">
                    <a class="custom-pagination-link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="{{ __('pagination.next') }}">
                        <span class="custom-pagination-arrow">&raquo;</span>
                    </a>
                </li>
            @else
                <li class="custom-pagination-item disabled" aria-disabled="true" aria-label="{{ __('pagination.next') }}">
                    <span class="custom-pagination-link"><span class="custom-pagination-arrow">&raquo;</span></span>
                </li>
            @endif
        </ul>
    </nav>
@endif
