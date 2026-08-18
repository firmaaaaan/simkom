@if ($paginator->hasPages())
    <nav>
        <ul class="custom-pagination-simple">
            @if ($paginator->onFirstPage())
                <li class="custom-pagination-item disabled" aria-disabled="true">
                    <span class="custom-pagination-link">
                        <span class="custom-pagination-arrow">&laquo;</span>
                        <span>Sebelumnya</span>
                    </span>
                </li>
            @else
                <li class="custom-pagination-item">
                    <a class="custom-pagination-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">
                        <span class="custom-pagination-arrow">&laquo;</span>
                        <span>Sebelumnya</span>
                    </a>
                </li>
            @endif

            @if ($paginator->hasMorePages())
                <li class="custom-pagination-item">
                    <a class="custom-pagination-link" href="{{ $paginator->nextPageUrl() }}" rel="next">
                        <span>Selanjutnya</span>
                        <span class="custom-pagination-arrow">&raquo;</span>
                    </a>
                </li>
            @else
                <li class="custom-pagination-item disabled" aria-disabled="true">
                    <span class="custom-pagination-link">
                        <span>Selanjutnya</span>
                        <span class="custom-pagination-arrow">&raquo;</span>
                    </span>
                </li>
            @endif
        </ul>
    </nav>
@endif
