@extends('admin.layout')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="d-flex justify-content-between align-items-center p-3">
                    <h1 class="mb-0 text-primary">Orders</h1>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="orders-table" class="table table-hover table-striped ">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Date</th>
                                    <th>Username</th>
                                    <th>Order</th>
                                    <th>Governorate</th>
                                    <th>Address</th>
                                    <th>University</th>
                                    <th>Phone</th>
                                    <th>Phone2</th>
                                    <th>Status</th>
                                    <th>Price</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Add Notes Modal --}}
    <div class="modal fade" id="addNoteModal" tabindex="-1" aria-labelledby="addNoteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addNoteModalLabel">Order Notes</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Add Note Card -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <form id="addNoteForm">
                                <input type="hidden" id="noteOrderId">
                                <div class="mb-3">
                                    <label for="noteContent" class="form-label">Enter your note</label>
                                    <textarea class="form-control" id="noteContent" rows="3" placeholder="Write your note here..."></textarea>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i> Save Note
                                    </button>
                                    <button type="button" class="btn btn-secondary" id="clearNoteBtn">
                                        <i class="fas fa-eraser me-2"></i> Clear
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Existing Notes Card -->
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <ul id="notesList" class="list-group">
                                <li class="text-muted">no notes yet</li>
                                {{-- notes will fetch here  --}}
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <link href="https://cdn.datatables.net/1.13.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.5/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#orders-table').DataTable({
                processing: true,
                serverSide: true,
                autoWidth: false,
                order: [
                    [0, 'desc']
                ],
                ajax: {
                    url: '{{ route('orders.fetch') }}',
                    data: function(d) {
                        // Append the selected status filter value to the request data
                        d.status = $('#statusFilter').val(); // Get the selected status filter
                        d.additives = $('#additivesFilter').val(); // Get the selected status filter
                    },
                    error: function(xhr, error, code) {
                        alert('An error occurred while fetching data. Please contact IT support.');
                    }
                },
                lengthMenu: [10, 25, 50, 100],
                pageLength: 10,
                columns: [{
                        data: 'id',
                        name: 'id',
                        render: function(data, type, row) {
                            return `<a href="/orders/${data}" class="btn btn-primary btn-sm">
                                            ${data}
                                    </a>`;
                        },
                        orderable: true,
                        searchable: false
                    },
                    {
                        data: 'data',
                        name: 'data',
                        orderable: false
                    },
                    {
                        data: 'username',
                        name: 'username',
                        orderable: false
                    },
                    {
                        data: 'order',
                        name: 'order',
                        orderable: false
                    },
                    {
                        data: 'governorate',
                        name: 'governorate',
                        orderable: false
                    },
                    {
                        data: 'address',
                        name: 'address',
                        orderable: false
                    },
                    {
                        data: 'school_name',
                        name: 'school_name',
                        orderable: false
                    },
                    {
                        data: 'phone',
                        name: 'phone',
                        orderable: false
                    },
                    {
                        data: 'phone2',
                        name: 'phone2',
                        orderable: false
                    },
                    {
                        data: 'status',
                        name: 'status',
                        render: function(data, type, row) {
                            const statusColors = {
                                Pending: 'bg-warning',
                                Completed: 'bg-success',
                                preparing: 'bg-purple',
                                'Out for Delivery': 'bg-pink',
                                Completed: 'bg-success',
                                Received: 'bg-primary',
                                Canceled: 'bg-danger'
                            };

                            const dropdownItems = ['Pending', 'preparing', 'Completed', 'Out for Delivery',
                                     'Received' ,'Canceled'
                                ]
                                .filter(status => status !== data)
                                .map(status => `
                                <li>
                                    <a class="dropdown-item change-status-item" 
                                       href="#" 
                                       data-order-id="${row.id}" 
                                       data-new-status="${status}">
                                       ${status}
                                    </a>
                                </li>
                            `).join('');

                            return `
                            <div class="dropdown">
                                <span 
                                    class="badge ${statusColors[data]} dropdown-toggle" 
                                    id="statusDropdown${row.id}" 
                                    data-bs-toggle="dropdown" 
                                    aria-expanded="false" 
                                    style="cursor: pointer;">
                                    ${data}
                                </span>
                                <ul class="dropdown-menu" aria-labelledby="statusDropdown${row.id}">
                                    ${dropdownItems}
                                </ul>
                            </div>`;
                        },
                        orderable: false
                    },
                    {
                        data: 'price',
                        name: 'price',
                        orderable: false
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        className: 'text-center',
                        orderable: false,
                        searchable: false
                    }
                ],
                language: {
                    search: "Search Orders:",
                    // processing: '<div class="spinner-border text-primary"></div>'
                },
                initComplete: function() {
                    // Create the status filter dropdown
                    var statusDropdown = $(
                        '<select id="statusFilter" class="form-select" style="width: 170px;height:34px; margin-left: 15px;">' +
                        '<option value="">Filter by Status</option>' +
                        '<option value="Pending">Pending</option>' +
                        '<option value="preparing">Preparing</option>' +
                        '<option value="Out for Delivery">Out for Delivery</option>' +
                        '<option value="Completed">Completed</option>' +
                        '<option value="Canceled">Canceled</option>' +
                        '</select>'
                    );

                    var additivesDropdown = $(
                        '<select id="additivesFilter" class="form-select" style="width: 175px;height:34px; margin-left: 15px;">' +
                        '<option value="">Filter by Additives</option>' +
                        '<option value="with_additives">With Additives</option>' +
                        '<option value="with_out_additives">With Out Additives</option>' +
                        '</select>'
                    );

                    // Style the dataTables_filter container with flexbox to align items horizontally
                    $('.dataTables_filter').css({
                        'display': 'flex',
                        'justify-content': 'flex-end',
                        'align-items': 'center'
                    });

                    // Append the dropdown next to the search input
                    $('.dataTables_filter').append(statusDropdown);
                    $('.dataTables_filter').append(additivesDropdown);

                    // When the status dropdown value changes, reload the table with the selected filter
                    $('#statusFilter').on('change', function() {
                        $('#orders-table').DataTable().ajax.reload();
                    });

                    $('#additivesFilter').on('change', function() {
                        $('#orders-table').DataTable().ajax.reload();
                    });
                }
            });

            // Change Status Event
            $(document).on('click', '.change-status-item', function(e) {
                e.preventDefault();

                const orderId = $(this).data('order-id');
                const newStatus = $(this).data('new-status');

                $.ajax({
                    url: '{{ route('orders.updateStatus') }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        id: orderId,
                        status: newStatus
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#orders-table').DataTable().ajax.reload();
                        } else {
                            alert('Failed to update order status. Please try again.');
                        }
                    },
                    error: function() {
                        alert('An error occurred while updating the status.');
                    }
                });
            });

            // Delete Order Event
            $(document).on('click', '.delete-order', function() {
                const orderId = $(this).data('id');

                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `/orders/${orderId}`,
                            type: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire('Deleted!', response.message, 'success');
                                    $('#orders-table').DataTable().ajax
                                        .reload(); // Refresh DataTable
                                } else {
                                    Swal.fire('Error!', 'Failed to delete the order.',
                                        'error');
                                }
                            },
                            error: function() {
                                Swal.fire('Error!',
                                    'An error occurred while deleting the order.',
                                    'error');
                            }
                        });
                    }
                });
            });
        });

        $(document).ready(function() {
            // Open Add Note Modal
            $(document).on('click', '.add-note', function() {
                const orderId = $(this).data('order-id');
                $('#noteOrderId').val(orderId); // Set the order id in the hidden input field

                // Clear existing notes and fetch notes for the order
                $('#notesList').html('<li class="list-group-item text-muted">Loading notes...</li>');
                $.ajax({
                    url: `/orders/${orderId}/notes`, // Adjust to your notes-fetching route
                    method: 'GET',
                    success: function(response) {
                        if (response.notes.length > 0) {
                            let notesHtml = '';
                            response.notes.forEach(note => {
                                notesHtml += `
                            <li class="list-group-item">
                                <strong>${note.user_name}</strong> 
                                <span class="text-muted">(${note.created_at})</span>
                                <p>${note.content}</p>
                            </li>
                        `;
                            });
                            $('#notesList').html(notesHtml);
                        } else {
                            $('#notesList').html(''); // Clear the list if no notes are found

                        }
                    },
                    error: function() {
                        $('#notesList').html(
                            '<li class="list-group-item text-danger">Failed to load notes.</li>'
                        );
                    },
                });

                $('#addNoteModal').modal('show');
            });

            // Add Note Form Submission
            $('#addNoteForm').on('submit', function(e) {
                e.preventDefault();

                const formData = {
                    _token: '{{ csrf_token() }}',
                    order_id: $('#noteOrderId').val(),
                    note: $('#noteContent').val(),
                };

                $.ajax({
                    url: '{{ route('orders.addNote') }}',
                    method: 'POST',
                    data: formData,
                    success: function(response) {
                        if (response.success) {
                            // Add the new note to the top of the notes list
                            const newNoteHtml = `
                        <li class="list-group-item">
                            <strong>${response.note.user_name}</strong> 
                            <span class="text-muted">(${response.note.created_at})</span>
                            <p>${response.note.content}</p>
                        </li>
                    `;
                            $('#notesList').prepend(newNoteHtml);
                            $('#noteContent').val(''); // Clear the note input

                            // Check if the list is now empty and update the message accordingly
                            if ($('#notesList li').length === 1 && $('#notesList li').hasClass(
                                    'text-muted')) {
                                $('#notesList').html(
                                    '<li class="list-group-item text-muted">No notes yet.</li>'
                                );
                            }
                        }
                    },
                    error: function(xhr) {
                        const errors = xhr.responseJSON?.errors || {};
                        if (errors.order_id) {
                            alert(errors.order_id[0]); // Show validation error
                        } else {
                            alert(
                                'An error occurred while adding the note.'
                            ); // Show general error
                        }
                    },
                });
            });

            // Clear the note input
            $('#clearNoteBtn').on('click', function() {
                $('#noteContent').val('');
            });
        });
    </script>
@endsection
