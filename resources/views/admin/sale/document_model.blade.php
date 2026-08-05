@if($salesMaster->pending_approvel== '1')
<form id="form1" class="form" action="{{route('application-save',$salesMaster->id)}}" method="POST">
    @csrf
    <div class="row">
        @if((isset($salesMaster) && isset($salesMaster->id)))
        <input type="hidden" id="sales_master_id" name="sales_master_id" value="{{ $salesMaster->id }}">
        @endif
        <div class="col-md-12 col-12">
            <button type="submit" class="btn btn-sm btn-primary float-end abc">Document Verified</button>
        </div>
    </div>
</form>
@endif