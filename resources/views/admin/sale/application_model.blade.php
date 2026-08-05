@if($salesMaster->application_pending == '1')
<form id="form" class="form" action="{{route('application-save',$salesMaster->id)}}" method="POST">
    @csrf
    <div class="row">
        @if((isset($salesMaster) && isset($salesMaster->id)))
        <input type="hidden" id="sales_master_id" name="sales_master_id" value="{{ $salesMaster->id }}">
        @endif
        <div class="col-12 col-md-12 mb-1 custom-input-group">
            <label class="form-label" for="ragistration_portal">Ragistration Portal</label>
            <div class="d-flex">
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="ragistration_portal" id="national" value="National">
                    <label class="form-check-label" for="national">National</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="ragistration_portal" id="grda" value="GEDA">
                    <label class="form-check-label" for="grda">GEDA</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="ragistration_portal" id="other" value="Other">
                    <label class="form-check-label" for="other">Other</label>
                </div>
                <!-- <input type="text" class="form-control" name="ragistration_portal" id="ragistration_portal" placeholder="Ragistration Portal *">
            <span class="invalid-feedback d-block" id="error_ragistration_portal" role="alert"></span> -->
            </div>
        </div>
        <div class="col-12 col-md-12 mb-1 custom-input-group">
            <label class="form-label" for="ragistration_numbar">Ragistration Numbar</label>
            <input type="text" class="form-control" name="ragistration_number" id="ragistration_number" placeholder="Ragistration Number *">
            <span class="invalid-feedback d-block" id="error_ragistration_number" role="alert"></span>
        </div>
        <div class="col-md-12 col-12">
            <button type="submit" class="btn btn-sm btn-primary float-end ">{{ __('message.Submit') }}</button>
        </div>
    </div>
</form>
@endif



@if($salesMaster->pending_approvel == '1')
<form id="form" class="form" action="{{route('application-save',$salesMaster->id)}}" method="POST">
    @csrf
    <div class="row">
        @if((isset($salesMaster) && isset($salesMaster->id)))
        <input type="hidden" id="sales_master_id" name="sales_master_id" value="{{ $salesMaster->id }}">
        @endif
        <div class="col-12 col-md-12 mb-1 custom-input-group">
            <label class="form-label" for="feasibility_discom_sr_number">Discom Sr Number </label>
            <input type="text" class="form-control" name="feasibility_discom_sr_number" id="feasibility_discom_sr_number" placeholder="Feasibility Discom Sr Number *" required>
            <span class="invalid-feedback d-block" id="error_feasibility_discom_sr_number" role="alert"></span>
        </div>
        <div class="col-12 col-md-12 mb-1 custom-input-group">
            <label class="form-label" for="feasibility_amount">Feasibility Amount</label>
            <input type="text" class="form-control" name="feasibility_amount" id="feasibility_amount" placeholder="Feasibility Amount*" required>
            <span class="invalid-feedback d-block" id="error_feasibility_amount" role="alert"></span>
        </div>
        <div class="col-md-12 col-12">
            <button type="submit" class="btn btn-sm btn-primary float-end">{{ __('message.Submit') }}</button>
        </div>
    </div>
</form>
@endif



<!-- @if($salesMaster->payment_receveid == '1')
<form id="form" class="form" action="{{route('application-save',$salesMaster->id)}}" method="POST">
    @csrf
    <div class="row">
        @if((isset($salesMaster) && isset($salesMaster->id)))
        <input type="hidden" id="sales_master_id" name="sales_master_id" value="{{ $salesMaster->id }}">
        @endif
        <div class="col-md-12 col-12">
            <button type="submit" class="btn btn-sm btn-primary float-end abc">{{ __('message.Submit') }}</button>
        </div>
    </div>
</form>
@else -->
<!-- Please Payment  -->
@endif

@if($salesMaster->dispach_pending_list == '1')
<form id="form" class="form" action="{{route('application-save',$salesMaster->id)}}" method="POST">
    @csrf
    <div class="row">
        @if((isset($salesMaster) && isset($salesMaster->id)))
        <input type="hidden" id="sales_master_id" name="sales_master_id" value="{{ $salesMaster->id }}">
        @endif
        <div class="col-md-12 col-12">
            <button type="submit" class="btn btn-sm btn-primary float-end ">Installation Pending</button>
        </div>
    </div>
</form>
@endif

@if($salesMaster->installation_pending == '1')
<form id="form" class="form" action="{{route('application-save',$salesMaster->id)}}" method="POST">
    @csrf
    <div class="row">
        @if((isset($salesMaster) && isset($salesMaster->id)))
        <input type="hidden" id="sales_master_id" name="sales_master_id" value="{{ $salesMaster->id }}">
        @endif
        <div class="col-12 col-md-12 mb-1 custom-input-group">
            <label class="form-label" for="invoice_no">Invoice No</label>
            <input type="text" class="form-control" name="invoice_no" id="invoice_no" placeholder="Invoice Number">
            <span class="invalid-feedback d-block" id="error_invoice_no" role="alert"></span>
        </div>
        <div class="col-12 col-md-12 mb-1 custom-input-group">
            <label class="form-label" for="invoice_date">Invoice Date</label>
            <input type="date" class="form-control flatpickr-basic invoice_date" name="invoice_date" id="invoice_date" placeholder="Invoice Date">
            <span class="invalid-feedback d-block" id="error_invoice_date" role="alert"></span>
        </div>
        <div class="col-12 col-md-12 mb-1 custom-input-group">
            <label class="form-label" for="discom_sr_numbar">Discom SR Numbar</label>
            <input type="text" class="form-control" name="discom_sr_numbar" id="discom_sr_numbar" placeholder="Discom SR Numbar">
            <span class="invalid-feedback d-block" id="error_discom_sr_numbar" role="alert"></span>
        </div>
        <div class="col-12 col-md-12 mb-1 custom-input-group">
            <label class="form-label" for="installation_asian_person">Installation Asian Person</label>
            <select class="form-control form-select select2 custom-select2" name="installation_asian_person" id="installation_asian_person">
                <option selected disabled>{{ __('message.-- Select --') }}</option>
                @foreach($user as $value)
                <option value="{{$value->id}}">{{$value->name}}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-12 col-12">
            <button type="submit" class="btn btn-sm btn-primary float-end ">{{ __('message.Submit') }}</button>
        </div>
    </div>
</form>
@endif

@if($salesMaster->installation_done == '1')
<form id="form" class="form" action="{{route('application-save',$salesMaster->id)}}" method="POST">
    @csrf
    <div class="row">
        @if((isset($salesMaster) && isset($salesMaster->id)))
        <input type="hidden" id="sales_master_id" name="sales_master_id" value="{{ $salesMaster->id }}">
        @endif

        <div class="col-12 col-md-12 mb-1 custom-input-group">
            <label class="form-label" for="installation_date">Installation Date</label>
            <input type="date" class="form-control flatpickr-basic installation_date" name="installation_date" id="installation_date" placeholder="Installation Date">
            <span class="invalid-feedback d-block" id="error_installation_date" role="alert"></span>
        </div>

        <div class="col-md-12 col-12">
            <button type="submit" class="btn btn-sm btn-primary float-end ">{{ __('message.Submit') }}</button>
        </div>
    </div>
</form>
@endif

@if($salesMaster->meter_application_done == '1')
<form id="form" class="form" action="{{route('application-save',$salesMaster->id)}}" method="POST">
    @csrf
    <div class="row">
        @if((isset($salesMaster) && isset($salesMaster->id)))
        <input type="hidden" id="sales_master_id" name="sales_master_id" value="{{ $salesMaster->id }}">
        @endif

        <div class="col-12 col-md-12 mb-1 custom-input-group">
            <label class="form-label" for="couriar_ditails">Couriar Ditails</label>
            <input type="text" class="form-control" name="couriar_ditails" id="couriar_ditails" placeholder="Couriar Ditails">
            <span class="invalid-feedback d-block" id="error_couriar_ditails" role="alert"></span>
        </div>

        <div class="col-12 col-md-12 mb-1 custom-input-group">
            <label class="form-label" for="couriar_no">Couriar No</label>
            <input type="text" class="form-control" name="couriar_no" id="couriar_no" placeholder="Couriar Number">
            <span class="invalid-feedback d-block" id="error_couriar_no" role="alert"></span>
        </div>
        <div class="col-12 col-md-12 mb-1 custom-input-group">
            <label class="form-label" for="courair_company">Courair Company</label>
            <input type="text" class="form-control" name="courair_company" id="courair_company" placeholder="Courair Company">
            <span class="invalid-feedback d-block" id="error_courair_company" role="alert"></span>
        </div>

        <div class="col-12 col-md-12 mb-1 custom-input-group">
            <label class="form-label" for="meter_application_date">meter_application Date</label>
            <input type="date" class="form-control flatpickr-basic meter_application_date" name="meter_application_date" id="meter_application_date" placeholder="Installation Date">
            <span class="invalid-feedback d-block" id="error_meter_application_date" role="alert"></span>
        </div>

        <div class="col-12 col-md-12 mb-1 custom-input-group">
            <label class="form-label" for="meter_asian_person">Meter Asian Person</label>
            <select class="form-control form-select select2 custom-select2" name="meter_asian_person" id="meter_asian_person">
                <option selected disabled>{{ __('message.-- Select --') }}</option>
                @foreach($user as $value)
                <option value="{{$value->id}}">{{$value->name}}</option>
                @endforeach
            </select>
        </div>


        <div class="col-md-12 col-12">
            <button type="submit" class="btn btn-sm btn-primary float-end ">{{ __('message.Submit') }}</button>
        </div>
    </div>
</form>
@endif

@if($salesMaster->meter_installation == '1')
<form id="form" class="form" action="{{route('application-save',$salesMaster->id)}}" method="POST">
    @csrf
    <div class="row">
        @if((isset($salesMaster) && isset($salesMaster->id)))
        <input type="hidden" id="sales_master_id" name="sales_master_id" value="{{ $salesMaster->id }}">
        @endif

        <div class="col-md-12 col-12">
            <button type="submit" class="btn btn-sm btn-primary text-center ">Subsidy Request</button>
        </div>
    </div>
</form>
@endif


@if($salesMaster->subsidy_claimed == '1')
<form id="form" class="form" action="{{route('application-save',$salesMaster->id)}}" method="POST">
    @csrf
    <div class="row">
        @if((isset($salesMaster) && isset($salesMaster->id)))
        <input type="hidden" id="sales_master_id" name="sales_master_id" value="{{ $salesMaster->id }}">
        @endif
        <!-- <div class="col-12 col-md-12 mb-1 custom-input-group">
            <label class="form-label" for="bank_name">Installation Date</label>
            <input type="text" class="form-control" name="installation_date" id="installation_date" placeholder="Installation Date" >
            <span class="invalid-feedback d-block" id="error_installation_date" role="alert"></span>
        </div>
        <div class="col-12 col-md-12 mb-1 custom-input-group">
            <label class="form-label" for="installation_date">Installation Date</label>
            <input type="date" class="form-control" name="installation_date" id="installation_date" placeholder="Installation Date" >
            <span class="invalid-feedback d-block" id="error_installation_date" role="alert"></span>
        </div>-->
        <!-- <div class="col-12 col-md-12 mb-1 custom-input-group">
            <label class="form-label" for="reson">Reson</label>
            <input type="text" class="form-control" name="reson" id="reson" placeholder="reson" >
            <span class="invalid-feedback d-block" id="error_reson" role="alert"></span>
        </div>  -->

        <div class="col-md-12 col-12">
            <button type="submit" class="btn btn-sm btn-primary ">Subsidy Receveid</button>
        </div>
    </div>
</form>
@endif

@if($salesMaster->subsidy_receveid == '1')
<form id="form" class="form" action="{{route('application-save',$salesMaster->id)}}" method="POST">
    @csrf
    <div class="row">
        @if((isset($salesMaster) && isset($salesMaster->id)))
        <input type="hidden" id="sales_master_id" name="sales_master_id" value="{{ $salesMaster->id }}">
        @endif
        <div class="col-12 col-md-12 mb-1 custom-input-group">
            <label class="form-label" for="reson">Reson</label>
            <input type="text" class="form-control" name="reson" id="reson" placeholder="reson">
            <span class="invalid-feedback d-block" id="error_reson" role="alert"></span>
        </div>

        <div class="col-md-12 col-12">
            <button type="submit" class="btn btn-sm btn-primary float-end ">{{ __('message.Submit') }}</button>
        </div>
    </div>
</form>
@endif