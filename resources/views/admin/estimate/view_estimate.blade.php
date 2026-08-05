@extends('layouts.app')
@section('title', 'Estimate')
@section('content')
<div class="row">
     <div class="col-12 mb-1">
          <h4 class="content-header-title float-start">{{ __('message.Estimate List') }}</small></h4>
          @can('estimate-create')
          <a role="button" class="btn btn-sm btn-primary float-end" href="{{route('estimate.create')}}"><i class="fa fa-plus me-25"></i> {{ __('message.Add New') }}</a>
          @endcan
     </div>
     <div class="col-12">
          <div class="card p-1">
               <table id="table" class="datatables-basic table table-hover">
                    <thead>
                         <tr>
                              <th>#</th>
                              <th>{{ __('message.Action') }}</th>
                              <th>{{ __('message.Client') }}</th>
                              <th>{{ __('message.Estimate Title') }}</th>
                              <th>{{ __('message.Estimate Date') }}</th>
                              <th>{{ __('message.Expiry Date') }}</th>
                         </tr>
                    </thead>
                    <tbody>

                    </tbody>
               </table>
          </div>
     </div>
</div>
@endsection

@section('pagescript')
<script type="text/javascript">
     'use strict';
     const URL = "{{route('estimate.index')}}";
     var table = '';
     $(function() {
          table = $('#table').DataTable({
               ajax: URL,
               processing: true,
               serverSide: true,
               fixedHeader: true,
               scrollX: true,
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
                         data: 'name',
                         name: 'name'
                    },
                    {
                         data: 'estimate_title',
                         name: 'estimate_title'
                    },
                    {
                         data: 'estimate_date',
                         name: 'estimate_date'
                    },
                    {
                         data: 'expiry_date',
                         name: 'expiry_date'
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