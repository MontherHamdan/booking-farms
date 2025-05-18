<div class="dropdown">
    <a class="dropdown-toggle" id="dropdownMenuButton{{ $order->id }}" data-bs-toggle="dropdown" style="cursor: pointer;"
        aria-expanded="false" title="Actions">
        <i class="fas fa-ellipsis-h"></i>
    </a>
    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton{{ $order->id }}">
        <li>
            <button class="dropdown-item text-danger delete-order" data-id="{{ $order->id }}">
                <i class="fas fa-trash me-2"></i>Delete
            </button>
        </li>
        <li>
            <button class="dropdown-item add-note" data-order-id="{{ $order->id }}" data-bs-toggle="modal"
                data-bs-target="#addNoteModal">
                <i class="fas fa-sticky-note me-2"></i>Add Notes
            </button>
        </li>
    </ul>
</div>
