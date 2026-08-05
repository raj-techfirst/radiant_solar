@extends('layouts.app')
@section('title', 'Notification')
@section('content')
<div class="row">
     <div class="col-12 mb-2">
          <h2 class="content-header-title float-start">View Notification</h2>
          <a role="button" class="btn btn-primary float-end" href="{{route('notification.create')}}">Add Notification</a>
     </div>
     <div class="col-12">
          <div class="card p-2">
               <table id="table" class="text-center datatables-basic table">
                    <thead>
                         <tr>
                              <th>#</th>
                              <th>Action</th>
                              <th>User</th>
                              <th>Title</th>
                              <th>Description</th>
                         </tr>
                    </thead>
                    <tbody>

                    </tbody>
               </table>
          </div>
     </div>
</div>
<!-- END: Content-->
@endsection

@section('pagescript')
<script type="application/javascript">
     'use strict';
     const URL = "{{route('notification.index')}}";

     var table = '';
     $(function() {
          table = $('#table').DataTable({
               ajax: URL,
               processing: true,
               serverSide: true,
               fixedHeader: true,
               scrollX: true,
               columns: [{
                         data: 'id',
                         render: function(data, type, row, meta) {
                              return meta.row + meta.settings._iDisplayStart + 1;
                         }
                    },
                    {
                         data: 'action',
                         name: 'action',
                         orderable: false,
                         sortable: false
                    },
                    {
                         data: 'user_id',
                         name: 'user_id'
                    },
                    {
                         data: 'title',
                         name: 'title'
                    },
                    {
                         data: 'description',
                         name: 'description'
                    },
               ],
               initComplete: function(settings, json) {
                    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                         return new bootstrap.Tooltip(tooltipTriggerEl)
                    })
               }
          });
     });

     $(document).on('click', '.delete', function() {
          var btn = $(this);
          var id = btn.data('id');
          Swal.fire({
                    title: "Are you sure?",
                    text: "You won`t be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: "Yes, delete it!",
                    customClass: {
                         confirmButton: 'btn btn-primary',
                         cancelButton: 'btn btn-outline-danger ms-1'
                    },
                    buttonsStyling: false
               })
               .then(function(result) {
                    if (result.value) {
                         axios.delete(URL + '/' + id)
                              .then(function(response) {
                                   if (response.data.status == true) {
                                        table.ajax.reload(null, false);
                                        toastr.success("Deleted successfully", "Success");
                                   } else {
                                        toastr.error("Something went wrong. Please try again.", "Error");
                                   }
                              })
                              .catch(function() {
                                   toastr.error("Something went wrong. Please try again.", "Error");
                              });
                    } else {
                         Swal.fire({
                              text: "Your data is safe."
                         });
                    }
               });
     });
</script>
@endsection