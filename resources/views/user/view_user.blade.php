@extends('layouts.app')
@section('title', 'User')
@section('content')
<div class="row">
     <div class="col-12 mb-1">
          <h4 class="content-header-title float-start">{{ __('message.View User') }}</h4>
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
                                   <th>{{ __('message.First Name') }}</th>
                                   <th>{{ __('message.Last Name') }}</th>
                                   <th>{{ __('message.Company Name') }}</th>
                                   <th>{{ __('message.Email') }}</th>
                                   <th>{{ __('message.Mobile') }}</th>
                                   <!-- <th>City</th> -->
                                   <!-- <th>State</th> -->
                              </tr>
                         </thead>
                         <tbody>

                         </tbody>
                    </table>
               </div>
          </div>
     </div>
</div>
@endsection

@section('pagescript')
<script type="text/javascript">
     'use strict';
     const URL = "{{route('user-list')}}";
     var table = '';
     $(function() {
          table = $('#table').DataTable({
               ajax: URL,
               processing: true,
               serverSide: true,
               fixedHeader: true,
               scroll: false,
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
                         data: 'name',
                         name: 'name'
                    },
                    {
                         data: 'last_name',
                         name: 'last_name'
                    },
                    {
                         data: 'company_name',
                         name: 'company_name'
                    },
                    {
                         data: 'email',
                         name: 'email'
                    },
                    {
                         data: 'mobile',
                         name: 'mobile'
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

     $(document).on('click', '.status', function() {
          let status = $(this).data('value');
          let id = $(this).data('id');
          var url = "{{route('user-status')}}";
          $.ajax({
               type: "POST",
               url: url,
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
                         toastr.error("{{ __('message.Something went wrong. Please try again.') }}", "{{ __('message.Error') }}");
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
                                   }
                                   else if (response.data.status == false && response.data.server_error) {
                                       toastr.error("{{ __('message.Something went wrong. Please try again.') }}", "{{ __('message.Error') }}");
                                   }
                                   else {
                                        toastr.warning("This is SELF LEAD account you can`t delete.", "{{ __('message.Warning') }}");
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