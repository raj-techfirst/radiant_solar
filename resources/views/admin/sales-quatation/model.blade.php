<div class="row ">
    @if($salesQuatation->mobile != "")
    <div class="col-lg-4">
        <ul class="p-0 m-0">
            <li class="d-flex pb-1 align-items-center">
                <div class="avatar bg-light-info me-1 rounded p-1">
                    <i data-feather='phone'></i>
                </div>
                <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                    <div class="me-2">
                        <h6 class="mb-0">{{$salesQuatation->mobile}}</h6>
                        <small class=" d-block">Mobile</small>
                    </div>
                </div>
            </li>
        </ul>
    </div>
    @endif
    @if($salesQuatation->name != "")
    <div class="col-lg-4">
        <ul class="p-0 m-0">
            <li class="d-flex pb-1 align-items-center">
                <div class="avatar bg-light-danger me-1 rounded p-1">
                    <i data-feather='users'></i>
                </div>
                <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                    <div class="me-2">
                        <h6 class="mb-0">{{$salesQuatation->name}}</h6>
                        <small class=" d-block">Name</small>
                    </div>
                </div>
            </li>
        </ul>
    </div>
    @endif
    @if($salesQuatation->address != "")
    <div class="col-lg-4">
        <ul class="p-0 m-0">
            <li class="d-flex pb-1 align-items-center">
                <div class="avatar bg-light-secondary me-1 rounded p-1">
                    <i data-feather='map-pin'></i>
                </div>
                <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                    <div class="me-2">
                        <h6 class="mb-0">{{$salesQuatation->address}}</h6>
                        <small class=" d-block">Address</small>
                    </div>
                </div>
            </li>
        </ul>
    </div>
    @endif
    @if($salesQuatation->ship_to != "")
    <div class="col-lg-4">
        <ul class="p-0 m-0">
            <li class="d-flex pb-1 align-items-center">
                <div class="avatar bg-light-info me-1 rounded p-1">
                    <i data-feather='shield'></i>
                </div>
                <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                    <div class="me-2">
                        <h6 class="mb-0">{{$salesQuatation->ship_to}}</h6>
                        <small class=" d-block">Ship to</small>
                    </div>
                </div>
            </li>
        </ul>
    </div>
    @endif
    @if($salesQuatation->gst_no != "")
    <div class="col-lg-4">
        <ul class="p-0 m-0">
            <li class="d-flex pb-1 align-items-center">
                <div class="avatar bg-light-danger me-1 rounded p-1">
                    <i data-feather='package'></i>
                </div>
                <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                    <div class="me-2">
                        <h6 class="mb-0">{{$salesQuatation->gst_no}}</h6>
                        <small class=" d-block">GST Number</small>
                    </div>
                </div>
            </li>
        </ul>
    </div>
    @endif
    @if($salesQuatation->item_id != "")
    <div class="col-lg-4">
        <ul class="p-0 m-0">
            <li class="d-flex pb-1 align-items-center">
                <div class="avatar bg-light-secondary me-1 rounded p-1">
                    <i data-feather='activity'></i>
                </div>
                <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                    <div class="me-2">
                        <h6 class="mb-0">{{$salesQuatation->item_id}}</h6>
                        <small class=" d-block">Item</small>
                    </div>
                </div>
            </li>
        </ul>
    </div>
    @endif
    @if($salesQuatation->nos != "")
    <div class="col-lg-4">
        <ul class="p-0 m-0">
            <li class="d-flex pb-1 align-items-center">
                <div class="avatar bg-light-info me-1 rounded p-1">
                    <i data-feather='codepen'></i>
                </div>
                <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                    <div class="me-2">
                        <h6 class="mb-0">{{$salesQuatation->nos}}</h6>
                        <small class=" d-block">Nos</small>
                    </div>
                </div>
            </li>
        </ul>
    </div>
    @endif
    @if($salesQuatation->rate != "")
    <div class="col-lg-4">
        <ul class="p-0 m-0">
            <li class="d-flex pb-1 align-items-center">
                <div class="avatar bg-light-danger me-1 rounded p-1">
                    <i data-feather='map'></i>
                </div>
                <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                    <div class="me-2">
                        <h6 class="mb-0">{{$salesQuatation->rate}}</h6>
                        <small class=" d-block">Rate</small>
                    </div>
                </div>
            </li>
        </ul>
    </div>
    @endif
    @if($salesQuatation->reference != "")
    <div class="col-lg-4">
        <ul class="p-0 m-0">
            <li class="d-flex pb-1 align-items-center">
                <div class="avatar bg-light-secondary me-1 rounded p-1">
                    <i data-feather='refresh-ccw'></i>
                </div>
                <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                    <div class="me-2">
                        <h6 class="mb-0">{{$salesQuatation->reference}}</h6>
                        <small class=" d-block">Reference</small>
                    </div>
                </div>
            </li>
        </ul>
    </div>
    @endif
    @if($salesQuatation->agent_sales_person_id != "")
    <div class="col-lg-4">
        <ul class="p-0 m-0">
            <li class="d-flex pb-1 align-items-center">
                <div class="avatar bg-light-info me-1 rounded p-1">
                    <i data-feather='user-check'></i>
                </div>
                <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                    <div class="me-2">
                        <h6 class="mb-0">{{$salesQuatation->agent_sales_people_name}}</h6>
                        <small class=" d-block">Agent Sales Person</small>
                    </div>
                </div>
            </li>
        </ul>
    </div>
    @endif
    @if($salesQuatation->penal_company_id != "")
    <div class="col-lg-4">
        <ul class="p-0 m-0">
            <li class="d-flex pb-1 align-items-center">
                <div class="avatar bg-light-info me-1 rounded p-1">
                    <i data-feather='menu'></i>
                </div>
                <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                    <div class="me-2">
                        <h6 class="mb-0">{{$salesQuatation->penal_company_id}}</h6>
                        <small class=" d-block">Panel Company</small>
                    </div>
                </div>
            </li>
        </ul>
    </div>
    @endif
    @if($salesQuatation->penal_type_id != "")
    <div class="col-lg-4">
        <ul class="p-0 m-0">
            <li class="d-flex pb-1 align-items-center">
                <div class="avatar bg-light-danger me-1 rounded p-1">
                    <i data-feather='type'></i>
                </div>
                <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                    <div class="me-2">
                        <h6 class="mb-0">{{$salesQuatation->penal_types_name}}</h6>
                        <small class=" d-block">Panel Type</small>
                    </div>
                </div>
            </li>
        </ul>
    </div>
    @endif
    @if($salesQuatation->penal_watt_id != "")
    <div class="col-lg-4">
        <ul class="p-0 m-0">
            <li class="d-flex pb-1 align-items-center">
                <div class="avatar bg-light-secondary me-1 rounded p-1">
                    <i data-feather='wind'></i>
                </div>
                <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                    <div class="me-2">
                        <h6 class="mb-0">{{$salesQuatation->penal_watts_name}}</h6>
                        <small class=" d-block">Panel Watt</small>
                    </div>
                </div>
            </li>
        </ul>
    </div>
    @endif
    @if($salesQuatation->penal_nos != "")
    <div class="col-lg-4">
        <ul class="p-0 m-0">
            <li class="d-flex pb-1 align-items-center">
                <div class="avatar bg-light-info me-1 rounded p-1">
                    <i data-feather='codepen'></i>
                </div>
                <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                    <div class="me-2">
                        <h6 class="mb-0">{{$salesQuatation->penal_nos}}</h6>
                        <small class=" d-block">Panel Nos</small>
                    </div>
                </div>
            </li>
        </ul>
    </div>
    @endif
    @if($salesQuatation->pv_capacity_kw != "")
    <div class="col-lg-4">
        <ul class="p-0 m-0">
            <li class="d-flex pb-1 align-items-center">
                <div class="avatar bg-light-danger me-1 rounded p-1">
                    <i data-feather='power'></i>
                </div>
                <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                    <div class="me-2">
                        <h6 class="mb-0">{{$salesQuatation->pv_capacity_kw}}</h6>
                        <small class=" d-block">PV Capacity Kw </small>
                    </div>
                </div>
            </li>
        </ul>
    </div>
    @endif
    @if($salesQuatation->inveter_company_id != "")
    <div class="col-lg-4">
        <ul class="p-0 m-0">
            <li class="d-flex pb-1 align-items-center">
                <div class="avatar bg-light-secondary me-1 rounded p-1">
                    <i data-feather='file'></i>
                </div>
                <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                    <div class="me-2">
                        <h6 class="mb-0">{{$salesQuatation->inveter_company_id}}</h6>
                        <small class=" d-block">Inverter Company</small>
                    </div>
                </div>
            </li>
        </ul>
    </div>
    @endif
    @if($salesQuatation->inveter_capacity != "")
    <div class="col-lg-4">
        <ul class="p-0 m-0">
            <li class="d-flex pb-1 align-items-center">
                <div class="avatar bg-light-info me-1 rounded p-1">
                    <i data-feather='file-plus'></i>
                </div>
                <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                    <div class="me-2">
                        <h6 class="mb-0">{{$salesQuatation->inveter_capacity}}</h6>
                        <small class=" d-block">Inverter Capacity</small>
                    </div>
                </div>
            </li>
        </ul>
    </div>
    @endif
    @if($salesQuatation->no_of_inveter != "")
    <div class="col-lg-4">
        <ul class="p-0 m-0">
            <li class="d-flex pb-1 align-items-center">
                <div class="avatar bg-light-danger me-1 rounded p-1">
                    <i data-feather='user-plus'></i>
                </div>
                <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                    <div class="me-2">
                        <h6 class="mb-0">{{$salesQuatation->no_of_inveter}}</h6>
                        <small class=" d-block">No Of Inverter</small>
                    </div>
                </div>
            </li>
        </ul>
    </div>
    @endif
    @if($salesQuatation->structure != "")
    <div class="col-lg-4">
        <ul class="p-0 m-0">
            <li class="d-flex pb-1 align-items-center">
                <div class="avatar bg-light-secondary me-1 rounded p-1">
                    <i data-feather='aperture'></i>
                </div>
                <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                    <div class="me-2">
                        <h6 class="mb-0">{{$salesQuatation->structure}}</h6>
                        <small class=" d-block">Structure</small>
                    </div>
                </div>
            </li>
        </ul>
    </div>
    @endif
    @if($salesQuatation->common_meter != "")
    <div class="col-lg-4">
        <ul class="p-0 m-0">
            <li class="d-flex pb-1 align-items-center">
                <div class="avatar bg-light-info me-1 rounded p-1">
                    <i data-feather='command'></i>
                </div>
                <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                    <div class="me-2">
                        <h6 class="mb-0">{{$salesQuatation->common_meter}}</h6>
                        <small class=" d-block">Common Meter</small>
                    </div>
                </div>
            </li>
        </ul>
    </div>
    @endif
    @if($salesQuatation->rate_per_kw != "")
    <div class="col-lg-4">
        <ul class="p-0 m-0">
            <li class="d-flex pb-1 align-items-center">
                <div class="avatar bg-light-danger me-1 rounded p-1">
                    <i data-feather='command'></i>
                </div>
                <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                    <div class="me-2">
                        <h6 class="mb-0">{{$salesQuatation->rate_per_kw}}</h6>
                        <small class=" d-block">Rate Per KW</small>
                    </div>
                </div>
            </li>
        </ul>
    </div>
    @endif
    @if($salesQuatation->gst != "")
    <div class="col-lg-4">
        <ul class="p-0 m-0">
            <li class="d-flex pb-1 align-items-center">
                <div class="avatar bg-light-secondary me-1 rounded p-1">
                    <i data-feather='package'></i>
                </div>
                <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                    <div class="me-2">
                        <h6 class="mb-0">{{$salesQuatation->gst}}</h6>
                        <small class=" d-block">GST</small>
                    </div>
                </div>
            </li>
        </ul>
    </div>
    @endif
    @if($salesQuatation->total_system_cost != "")
    <div class="col-lg-4">
        <ul class="p-0 m-0">
            <li class="d-flex pb-1 align-items-center">
                <div class="avatar bg-light-danger me-1 rounded p-1">
                    <i data-feather='package'></i>
                </div>
                <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                    <div class="me-2">
                        <h6 class="mb-0">{{$salesQuatation->total_system_cost}}</h6>
                        <small class=" d-block">Total System Cost</small>
                    </div>
                </div>
            </li>
        </ul>
    </div>
    @endif
    @if($salesQuatation->meter_charges != "")
    <div class="col-lg-4">
        <ul class="p-0 m-0">
            <li class="d-flex pb-1 align-items-center">
                @if($salesQuatation->form_type == "resident")
                <div class="avatar bg-light-secondary me-1 rounded p-1">
                    @else
                    <div class="avatar bg-light-info me-1 rounded p-1">
                        @endif
                        <i data-feather='octagon'></i>
                    </div>
                    <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                        <div class="me-2">
                            <h6 class="mb-0">{{$salesQuatation->meter_charges}}</h6>
                            <small class=" d-block">Meter Charges </small>
                        </div>
                    </div>
            </li>
        </ul>
    </div>
    @endif
    @if($salesQuatation->registration_fee != "")
    <div class="col-lg-4">
        <ul class="p-0 m-0">
            <li class="d-flex pb-1 align-items-center">
                @if($salesQuatation->form_type == "resident")
                <div class="avatar bg-light-info me-1 rounded p-1">
                    @else
                    <div class="avatar bg-light-danger me-1 rounded p-1">
                        @endif
                        <i data-feather='sliders'></i>
                    </div>
                    <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                        <div class="me-2">
                            <h6 class="mb-0">{{$salesQuatation->registration_fee}}</h6>
                            <small class=" d-block">Registration Fee</small>
                        </div>
                    </div>
            </li>
        </ul>
    </div>
    @endif
    @if($salesQuatation->quatation_type != "")
    <div class="col-lg-4">
        <ul class="p-0 m-0">
            <li class="d-flex pb-1 align-items-center">
                <div class="avatar bg-light-secondary me-1 rounded p-1">
                    <i data-feather='info'></i>
                </div>
                <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                    <div class="me-2">
                        <h6 class="mb-0">{{$salesQuatation->quatation_type}}</h6>
                        <small class=" d-block">Quatation Type</small>
                    </div>
                </div>
            </li>
        </ul>
    </div>
    @endif
    @if(!empty($salesQuatation['company']))
    @foreach($salesQuatation['company'] as $key => $item)
    <div class="col-lg-4">
        <ul class="p-0 m-0">
            <li class="d-flex pb-1 align-items-center">
                @if(!is_null($item['logo']))
                <div class="avatar bg-light-secondary me-1 rounded">
                    <img src="{{asset('upload/company/'.$item['logo'])}}" class="img-fluid" height="45" width="45">
                </div>
                @else
                <div class="avatar bg-light-secondary me-1 rounded p-1">
                    <i data-feather='image'></i>
                </div>
                @endif
                <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                    <div class="me-2">
                        <h6 class="mb-0">{{$item['name']}}</h6>
                        <small class=" d-block">{{$key+1}} : Company Panel</small>
                    </div>
                </div>
            </li>
        </ul>
    </div>
    @endforeach
    @endif
</div>
@if($meta != "")
<div class="table-responsive">
    <table id="table" class="datatables-basic table table-hover">
        <thead>
            <tr>
                <th>#</th>
                <th>Item</th>
                <th>Nos</th>
                <th>Rate</th>
            </tr>
        </thead>
        <tbody>
            @foreach($meta as $key => $value)
            <tr>
                <th>{{$key+1}}</th>
                @if($value->type == "Item")
                <th class="text-nowrap">{{$value->item->name}} <br>HSN/SAC Code: {{$value->item->hsn_code}}</th>
                @else
                <th class="text-nowrap">{{getItemGropName($value,1)}} <br>HSN/SAC Code: {{$value->itemGroup->hsn_code}}</th>
                @endif
                <th>{{$value->nos}}</th>
                <th>{{$value->rate}}</th>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif