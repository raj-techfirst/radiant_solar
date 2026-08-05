@extends('layouts.app')
@section('title', 'Users')
@section('content')
<div class="row">
     <div class="col-12 mb-1">
          <h4 class="content-header-title float-start">Users List</h4>
          @can('employee-create')
          <a role="button" class="btn btn-sm btn-primary float-end" href="{{route('employee.create')}}"><i class="fa fa-plus me-25"></i> {{ __('message.Add New') }}</a>
          @endcan
     </div>
     <div class="col-12">
          <div class="card p-1">
               <div class="table-responsive">
                    <table id="table" class="datatables-basic table table-hover">
                         <thead>
                              <tr>
                                   <th>#</th>
                                   <th>{{ __('message.Action') }}</th>
                                   <th>{{ __('message.Status') }}</th>
                                   <th>{{ __('message.Type') }}</th>
                                   <th>{{ __('message.Name') }}</th>
                                   <th>{{ __('message.Manager') }}</th>
                                   <th>{{ __('message.Mobile') }}</th>
                                   <th>{{ __('message.Email') }}</th>
                              </tr>
                         </thead>
                         <tbody>

                         </tbody>
                    </table>
               </div>
          </div>
     </div>
</div>

<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
     <div class="modal-dialog modal-dialog-centered modal-lg">
          <div class="modal-content">
               <div class="modal-header bg-transparent border-bottom">
                    <h4 class="text-center mb-0" id="exampleModalTitle">{{ __('message.Details') }}</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
               </div>
               <div class="modal-body p-0" id="body">

               </div>
          </div>
     </div>
</div>
@endsection

@section('pagescript')
<script type="text/javascript">
     'use strict';
     const URL = "{{route('employee.index')}}";
     var table = '';
     $(function() {
          table = $('#table').DataTable({
               ajax: URL,
               processing: true,
               serverSide: true,
               fixedHeader: false,
               scroll: false,
               aLengthMenu: [
                    [20, -1],
                    [20, "All"],
               ],
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
                         data: 'status',
                         name: 'status'
                    },
                    {
                         data: 'user_type',
                         name: 'user_type'
                    },
                    {
                         data: 'name',
                         name: 'name'
                    },
                    {
                         data: 'manager_id',
                         name: 'manager_id'
                    },
                    {
                         data: 'user.mobile',
                         name: 'user.mobile'
                    },
                    {
                         data: 'user.email',
                         name: 'user.email'
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

     $(document).on('click', '.view', function(e) {
          e.preventDefault();
          var id = $(this).data('id');
          var url = "{{route('employee.update','id')}}".replace('id', id);
          $("#exampleModal").modal("show");
          $.ajax({
               url: url,
               type: 'PUT',
               datatype: 'json',
               data: {
                    "_token": "{{ csrf_token() }}",
               },
               success: function(response) {
                    $("#body").html(response.html);
               }
          });
     });

     $(document).on('click', '.status', function() {
          let status = $(this).data('value');
          let id = $(this).data('id');
          var route = "{{route('employee.show','id')}}".replace('id', id);
          $.ajax({
               type: "get",
               url: route,
               dataType: 'json',
               data: {
                    "_token": "{{ csrf_token() }}",
                    "id": id,
                    "status": status,
               },
               success: function(response) {
                    if (response.status == true) {
                         table.ajax.reload(null, false);
                         toastr.success("{{ __('message.Status updated successfully.') }}", "{{ __('message.Success') }}");
                    } else {
                         toastr.error("{{ __('message.Something wen`t wrong. Please try again.') }}", "{{ __('message.Error') }}");
                    }
               },
               error: function(error) {
                    toastr.error("{{ __('message.Something went wrong. Please try again.') }}", "{{ __('message.Error') }}");
                    $(document.body).css('pointer-events', '');
               }
          });
     });

     $(document).on('click', '.delete', function() {
          var btn = $(this);
          var id = btn.data('id');
          Swal.fire({
                    title: "{{ __('message.Are you sure?') }}",
                    text: "{{ __('message.You won`t be able to revert this!') }}",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: "{{ __('message.Yes, delete it!') }}",
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
                                        toastr.success("{{ __('message.Deleted successfully.') }}", "{{ __('message.Success') }}");
                                   } else if (response.data.status == false && response.data.server_error) {
                                        toastr.error("{{ __('message.Something went wrong. Please try again.') }}", "{{ __('message.Error') }}");
                                   } else {
                                        toastr.warning("{{ __('message.This employee activity has been used.') }}", "{{ __('message.Warning') }}");
                                   }
                              })
                              .catch(function() {
                                   toastr.error("{{ __('message.Something went wrong. Please try again.') }}", "{{ __('message.Error') }}");
                              });
                    } else {
                         Swal.fire({
                              text: "{{ __('message.Your data is safe.') }}"
                         });
                    }
               });
     });

     $('.table-responsive').on('show.bs.dropdown', function() {
          $('.table-responsive').css("overflow", "inherit");
     });

     $('.table-responsive').on('hide.bs.dropdown', function() {
          $('.table-responsive').css("overflow", "auto");
     });
</script>
@endsection