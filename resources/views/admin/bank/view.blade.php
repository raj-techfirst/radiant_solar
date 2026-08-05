@extends('layouts.app')
@section('title', 'Bank')
@section('content')
<div class="row">
     <div class="col-12 mb-1">
          <h2 class="content-header-title float-start">View Bank</h2>
          <a role="button" class="btn btn-primary btn-sm float-end" href="{{route('bank.create')}}">Add Bank</a>
     </div>
     
     <div class="col-12">
          <div class="card p-2">
               <table id="table" class="text-center datatables-basic table">
                    <thead>
                         <tr>
                              <th>#</th>
                              <th>{{ __('message.Action') }}</th>
                              <th>Bank Name</th>
                              <th>Account Number</th>
                              <th>IFSC Code</th>
                              <th>Branch</th>
                              <th>Default</th>
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
     const URL = "{{route('bank.index')}}";

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
                         data: 'name',
                         name: 'name'
                    },
                    {
                         data: 'account_number',
                         name: 'account_number'
                    },
                    {
                         data: 'ifsc_number',
                         name: 'ifsc_number'
                    },
                    {
                         data: 'branch',
                         name: 'branch'
                    },
                    {
                         data: 'default',
                         name: 'default'
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
                                   } else {
                                        toastr.error("{{ __('message.Something went wrong. Please try again.') }}", "{{ __('message.Error') }}");
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
</script>
@endsection