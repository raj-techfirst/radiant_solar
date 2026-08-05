@extends('layouts.app')
@section('title', 'Installation Edit')
@section('content')
<style>
    #inverter_table.form-table>:not(caption)>*>*,
    #order_table.form-table>:not(caption)>*>* {
        padding: 0.2rem 0.2rem !important;
    }
</style>
<div class="row">
    <div class="col-12">
        <h4 class="card-title my-25">Installation Edit</h4>
    </div>
    <div class="col-12">
        <div class="card p-0">
            <div class="bs-stepper vertical wizard-vertical-icons-example">
                <div class="bs-stepper-header p-1">
                    <div class="step active" data-target="#master">
                        <button type="button" class="step-trigger p-50" aria-selected="true">
                            <span class="bs-stepper-box">1</span>
                            <span class="bs-stepper-label">
                                <span class="bs-stepper-title">Module Details</span>
                                <span class="bs-stepper-subtitle">Module Details</span>
                            </span>
                        </button>
                    </div>
                    <div class="line"></div>
                    <div class="step" data-target="#policy">
                        <button type="button" class="step-trigger p-50" aria-selected="false">
                            <span class="bs-stepper-box">2</span>
                            <span class="bs-stepper-label">
                                <span class="bs-stepper-title">Inverter Details</span>
                                <span class="bs-stepper-subtitle">Inverter Details</span>
                            </span>
                        </button>
                    </div>

                    @php $j=3; @endphp
                    @foreach($itemStock as $wKey => $wValue)
                    <div class="line"></div>
                    <div class="step" data-target="#item_info_{{ $j }}">
                        <button type="button" class="step-trigger p-50" aria-selected="false">
                            <span class="bs-stepper-box"> {{ $j }}</span>
                            <span class="bs-stepper-label">
                                <span class="bs-stepper-title"> {{ $wValue['name'] }} Details</span>
                                <span class="bs-stepper-subtitle">{{ $wValue['name'] }} Details</span>
                            </span>
                        </button>
                    </div>
                    @php $j++; @endphp
                    @endforeach

                    <div class="line"></div>
                    <div class="step" data-target="#remark">
                        <button type="button" class="step-trigger p-50" aria-selected="false">
                            <span class="bs-stepper-box">{{ $j }}</span>
                            <span class="bs-stepper-label">
                                <span class="bs-stepper-title">Earthing Details</span>
                                <span class="bs-stepper-subtitle">Earthing Details</span>
                            </span>
                        </button>
                    </div>
                    <div class="line"></div>
                    <div class="step" data-target="#document">
                        <button type="button" class="step-trigger p-50" aria-selected="false">
                            <span class="bs-stepper-box">{{ $j+1 }}</span>
                            <span class="bs-stepper-label">
                                <span class="bs-stepper-title">Document Details</span>
                                <span class="bs-stepper-subtitle">Upload document</span>
                            </span>
                        </button>
                    </div>
                </div>
                <div class="bs-stepper-content p-1">
                    <form class="form-repeaters" id="form" action="javascript:void(0)" method="POST" enctype="multipart/form-data">
                        @csrf
                        <!-- Module Details -->
                        <div id="master" class="content active dstepper-block">
                            <input type="hidden" name="sales_master_id" value="{{Request::segment(2)}}">
                            <input type="hidden" name="id" value="{{ (isset($installation) && $installation->id) ? $installation->id : '' }}">

                            @if(isset($itemGroupStock['panel_already']))
                            @foreach($itemGroupStock['panel_already'] as $wiKey => $wiValue)
                            <div class="w-100 pt-3" id="panel_appends_{{$wiKey}}">
                                <div class="row g-1 mb-2 panel_append_items border border-bottom-dark" id="panel_append_{{$wiKey}}">
                                    <div class="col-12 col-md-6 col-lg-6 my-25 custom-input-group">
                                        <input type="hidden" name="panel_watt[]" id="panel_watt_{{$wiKey}}" value="">
                                        <label class="form-label w-100" for="stock_type_{{$wiKey}}">Item<span class="text-danger">*</span> &nbsp; <b class="float-end">Required Stock : <span id="required_{{$wiKey}}">{{ $wiValue['required_qty'] }}</span></b></label>
                                        <select class="form-select select2 panel_item" name="panel_item[]">
                                            <option disabled>-- Select --</option>
                                            <option selected value="{{ $wiValue['issue_type'] }}~{{ $wiValue['id'] }}" data-require="{{ $wiValue['required_qty'] }}" data-key="{{$wiKey}}" data-stock="{{ $wiValue['stock_qty'] }}" data-panelwatt="{{ $wiValue['panel_watt'] }}">{{ ucfirst($wiValue['issue_type']) }} - {{ $wiValue['name'] }} </option>
                                        </select>
                                        <span class="invalid-feedback d-block" id="error_panel_item" role="alert"></span>
                                    </div>
                                    <div class="col-12 col-md-6 col-lg-6 my-25 custom-input-group">
                                        <label class="form-label w-100" for="no_of_modules">Use Stock <span class="text-danger">*</span> &nbsp; <b class="float-end">Available Stock : <span id="available_{{$wiKey}}">{{ $wiValue['stock_qty'] }}</span></b></label>
                                        <input type="number" max="{{ ($wiValue['required_qty'] + $wiValue['stock_qty']) }}" class="form-control no_of_modules" name="no_of_modules[]" id="no_of_modules_{{$wiKey}}" data-key="{{$wiKey}}" placeholder="Use Stock" value="{{ (isset($wiValue['data']) && isset($wiValue['data'][0]->use_stock)) ? $wiValue['data'][0]->use_stock : ''  }}">
                                        <span class="invalid-feedback d-block" id="error_no_of_modules" role="alert"></span>
                                    </div>
                                    <div class="col-12 col-md-6 col-lg-6 my-25 custom-input-group">
                                        <label class="form-label" for="panel_model_number">Model Number <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control text-uppercase" name="panel_model_number[]" id="panel_model_number" placeholder="Model Number" value="{{ (isset($wiValue['data']) && isset($wiValue['data'][0]->model_number)) ? $wiValue['data'][0]->model_number : ''  }}">
                                        <span class="invalid-feedback d-block" id="error_panel_model_number" role="alert"></span>
                                    </div>
                                    <div class="col-12 col-md-6 col-lg-6 my-25 custom-input-group">
                                        <label class="form-label" for="total_kw">Total KW</label>
                                        <input type="number" class="form-control" name="total_kw[]" id="total_kw_{{$wiKey}}" placeholder="Total KW" value="{{ (isset($wiValue['data']) && isset($wiValue['data'][0]->total_kw)) ? $wiValue['data'][0]->total_kw : ''  }}" readonly>
                                    </div>
                                    <div class="col-12 col-md-12 col-lg-12 my-25 custom-input-group">
                                        <div class="row g-1 mt-25 panel_sr_module" id="append_box_{{$wiKey}}">
                                            @if((isset($wiValue['data']) && isset($wiValue['data'][0]->data)))
                                            @foreach($wiValue['data'][0]->data as $key => $item)
                                            <div class="col-12 col-md-6 col-lg-4 my-25 custom-input-group">
                                                <label class="form-label">Sr Number {{ $key+1 }} <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control text-uppercase" name="panel_sr_module[0][]" placeholder="Sr Number {{ $key+1 }}" value="{{ $item->serial_no }}" required>
                                            </div>
                                            @endforeach
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                            @endif

                            @if(!isset($itemGroupStock['panel_already']) && isset($itemGroupStock['panel']))

                            <div class="w-100 pt-3" id="panel_appends_0">
                                <div class="row g-1 mb-2 panel_append_items border border-bottom-dark" id="panel_append_0">
                                    <div class="col-12 col-md-6 col-lg-6 my-25 custom-input-group">
                                        <input type="hidden" name="panel_watt[]" id="panel_watt_0" value="">
                                        <label class="form-label w-100" for="stock_type_0">Item<span class="text-danger">*</span> &nbsp; <b class="float-end">Required Stock : <span id="required_0">0</span></b></label>
                                        <select class="form-select select2 panel_item" name="panel_item[]">
                                            <option selected disabled>-- Select --</option>
                                            @foreach($itemGroupStock['panel'] as $wiKey => $wiValue)
                                            <option value="{{ $wiValue['issue_type'] }}~{{ $wiValue['id'] }}" data-require="{{ $wiValue['required_qty'] }}" data-key="0" data-stock="{{ $wiValue['stock_qty'] }}" data-panelwatt="{{ $wiValue['panel_watt'] }}">{{ ucfirst($wiValue['issue_type']) }} - {{ $wiValue['name'] }} </option>
                                            @endforeach
                                        </select>
                                        <span class="invalid-feedback d-block" id="error_panel_item" role="alert"></span>
                                    </div>
                                    <div class="col-12 col-md-6 col-lg-6 my-25 custom-input-group">
                                        <label class="form-label w-100" for="no_of_modules">Use Stock <span class="text-danger">*</span> &nbsp; <b class="float-end">Available Stock : <span id="available_0">0</span></b></label>
                                        <input type="number" max="0" class="form-control no_of_modules" name="no_of_modules[]" id="no_of_modules_0" data-key="0" placeholder="Use Stock">
                                        <span class="invalid-feedback d-block" id="error_no_of_modules" role="alert"></span>
                                    </div>
                                    <div class="col-12 col-md-6 col-lg-6 my-25 custom-input-group">
                                        <label class="form-label" for="panel_model_number">Model Number <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control text-uppercase" name="panel_model_number[]" id="panel_model_number" placeholder="Model Number">
                                        <span class="invalid-feedback d-block" id="error_panel_model_number" role="alert"></span>
                                    </div>
                                    <div class="col-12 col-md-6 col-lg-6 my-25 custom-input-group">
                                        <label class="form-label" for="total_kw">Total KW</label>
                                        <input type="number" class="form-control" name="total_kw[]" id="total_kw_0" placeholder="Total KW" readonly>
                                    </div>
                                    <div class="col-12 col-md-12 col-lg-12 my-25 custom-input-group">
                                        <div class="row g-1 mt-25 panel_sr_module" id="append_box_0">

                                        </div>
                                    </div>

                                    <div class="col-12 col-md-12 col-lg-12 my-25 custom-input-group add-panel-buttons text-end">
                                    </div>

                                </div>

                            </div>
                            <div class="w-100">
                                <div class="col-12 col-md-12 col-lg-12 my-25 custom-input-group text-end">
                                    <button class="badge badge-light-success border-0 add-new-panel m-0" type="button">+ Add More</button>
                                </div>
                            </div>
                            @endif
                            <div class="row my-50">
                                <hr>
                                <div class="col-12">
                                    <button class="btn btn-sm btn-primary float-end btn-next">Next <i data-feather='arrow-right'></i></button>
                                </div>
                            </div>
                        </div>
                        <!-- Inverter Details -->
                        <div id="policy" class="content">

                            @if(isset($itemGroupStock['inverter_already']))
                            @foreach($itemGroupStock['inverter_already'] as $wiKey => $wiValue)
                            <div class="w-100 pt-3" id="inverter_appends_{{$wiKey}}">
                                <div class="row g-1 mb-2 inverter_append_items border border-bottom-dark" id="inverter_append_{{$wiKey}}">
                                    <div class="col-12 col-md-6 col-lg-6 my-25 custom-input-group">
                                        <label class="form-label w-100" for="inverter_item">Item<span class="text-danger">*</span> &nbsp; <b class="float-end">Required Stock : <span id="inv_required_{{$wiKey}}">{{ $wiValue['required_qty'] }}</span></b></label>
                                        <select class="form-select select2 inverter-item" name="inverter_item[]">
                                            <option disabled>-- Select --</option>
                                            <option selected value="{{ $wiValue['issue_type'] }}~{{ $wiValue['id'] }}" data-require="{{ $wiValue['required_qty'] }}" data-key="{{$wiKey}}" data-stock="{{ $wiValue['stock_qty'] }}">{{ ucfirst($wiValue['issue_type']) }} - {{ $wiValue['name'] }} </option>
                                        </select>
                                        <span class="invalid-feedback d-block" id="error_inverter_item" role="alert"></span>
                                    </div>
                                    <div class="col-12 col-md-6 col-lg-6 my-25 custom-input-group">
                                        <label class="form-label w-100" for="no_of_inverter">Use Stock <span class="text-danger">*</span> &nbsp; <b class="float-end">Available Stock : <span id="inv_available_{{$wiKey}}">{{ $wiValue['stock_qty'] }}</span></b></label>
                                        <input type="number" max="{{ $wiValue['required_qty'] + $wiValue['stock_qty'] }}" class="form-control no_of_inverter" data-key="{{$wiKey}}" name="no_of_inverter[]" id="no_of_inverter_{{$wiKey}}" placeholder="Use Stock" value="{{ count($wiValue['data']) }}">
                                        <span class="invalid-feedback d-block" id="error_no_of_inverter" role="alert"></span>
                                    </div>
                                    <div class="col-12 col-md-12 col-lg-12 my-25 custom-input-group">
                                        <div class="table-responsives">
                                            <table class="table form-table table-sm table-bordered" id="inverter_table_{{$wiKey}}">
                                                <thead>
                                                    <tr>
                                                        <th width="3%" class="text-center">#</th>
                                                        <th width="20%">Model Number <span class="text-danger">*</span></th>
                                                        <th width="20%">Sr Number <span class="text-danger">*</span></th>
                                                        <th width="17%">Voltage <span class="text-danger">*</span></th>
                                                    </tr>
                                                </thead>
                                                <tbody id="append_inverter_{{$wiKey}}" class="append_inverter">
                                                    @if(isset($wiValue['data']) && count($wiValue['data']))
                                                    @foreach($wiValue['data'] as $key => $item)
                                                    <tr>
                                                        <td class="text-center">{{ $key+1 }}</td>
                                                        <td class="custom-input-group">
                                                            <input type="text" class="form-control text-uppercase" name="inverter_model_number[{{$wiKey}}][]" placeholder="Model Number" value="{{ $item->model_number }}" required="">
                                                        </td>
                                                        <td class="custom-input-group">
                                                            <input type="text" class="form-control text-uppercase" name="inverter_sr_number[{{$wiKey}}][]" placeholder="Sr Number" value="{{ $item->serial_no_of_inverter }}" required="">
                                                        </td>
                                                        <td class="custom-input-group">
                                                            <input type="text" class="form-control text-uppercase" name="inverter_voltage[{{$wiKey}}][]" placeholder="Voltage" value="{{ $item->voltage }}" required="">
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                    @endif
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-12 col-lg-12 my-25 custom-input-group add-inverter-buttons text-end">

                                    </div>
                                </div>
                            </div>
                            @endforeach
                            @endif


                            @if(!isset($itemGroupStock['inverter_already']) && isset($itemGroupStock['inverter']))

                            <div class="w-100 pt-3" id="inverter_appends_0">
                                <div class="row g-1 mb-2 inverter_append_items border border-bottom-dark" id="inverter_append_0">

                                    <div class="col-12 col-md-6 col-lg-6 my-25 custom-input-group">
                                        <label class="form-label w-100" for="inverter_item">Item<span class="text-danger">*</span> &nbsp; <b class="float-end">Required Stock : <span id="inv_required_0">0</span></b></label>
                                        <select class="form-select select2 inverter-item" name="inverter_item[]">
                                            <option selected disabled>-- Select --</option>
                                            @foreach($itemGroupStock['inverter'] as $wiKey => $wiValue)
                                            <option value="{{ $wiValue['issue_type'] }}~{{ $wiValue['id'] }}" data-require="{{ $wiValue['required_qty'] }}" data-key="0" data-stock="{{ $wiValue['stock_qty'] }}">{{ ucfirst($wiValue['issue_type']) }} - {{ $wiValue['name'] }} </option>
                                            @endforeach
                                        </select>
                                        <span class="invalid-feedback d-block" id="error_inverter_item" role="alert"></span>
                                    </div>
                                    <div class="col-12 col-md-6 col-lg-6 my-25 custom-input-group">
                                        <label class="form-label w-100" for="no_of_inverter">Use Stock <span class="text-danger">*</span> &nbsp; <b class="float-end">Available Stock : <span id="inv_available_0">0</span></b></label>
                                        <input type="number" max="0" class="form-control no_of_inverter" data-key="0" name="no_of_inverter[]" id="no_of_inverter_0" placeholder="Use Stock">
                                        <span class="invalid-feedback d-block" id="error_no_of_inverter" role="alert"></span>
                                    </div>
                                    <div class="col-12 col-md-12 col-lg-12 my-25 custom-input-group">
                                        <div class="table-responsives">
                                            <table class="table form-table table-sm table-bordered d-none" id="inverter_table_0">
                                                <thead>
                                                    <tr>
                                                        <th width="3%" class="text-center">#</th>
                                                        <th width="20%">Model Number <span class="text-danger">*</span></th>
                                                        <th width="20%">Sr Number <span class="text-danger">*</span></th>
                                                        <th width="17%">Voltage <span class="text-danger">*</span></th>
                                                    </tr>
                                                </thead>
                                                <tbody id="append_inverter_0" class="append_inverter">

                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-12 col-lg-12 my-25 custom-input-group add-inverter-buttons text-end">

                                    </div>
                                </div>
                            </div>

                            <div class="w-100">
                                <div class="col-12 col-md-12 col-lg-12 my-25 custom-input-group text-end">
                                    <button class="badge badge-light-success border-0 add-new-inverter m-0" type="button">+ Add More</button>
                                </div>
                            </div>

                            @endif
                            <div class="row my-50">
                                <hr>
                                <div class="col-12">
                                    <button class="btn btn-sm btn-secondary btn-prev"><i data-feather='arrow-left'></i> Previous</button>
                                    <button class="btn btn-sm btn-primary float-end btn-next">Next <i data-feather='arrow-right'></i></button>
                                </div>
                            </div>
                        </div>
                        @php $j=3; @endphp
                        @foreach($itemStock as $wKey => $wValue)
                        <!-- Items Details -->
                        <div id="item_info_{{ $j }}" class="content">
                            @foreach($wValue['items'] as $wiKey => $wiValue)
                            <div class="w-100 pt-1" id="item-row-0">
                                <div class="row g-1">
                                    <div class="col-12 col-md-6 col-lg-6 my-25 custom-input-group">
                                        <label class="form-label w-100" for="stock_type">Item <b class="float-end">Required : {{ $wiValue['required_qty'] }} {{ $wiValue['unit'] }}</b></label>
                                        <input type="hidden" name="item_ids[]" value="{{ $wiValue['id'] }}" />
                                        <select class="form-select select2" disabled name="stock_type[]" id="stock_type">
                                            <option value="{{ $wiValue['id'] }}" selected disabled>{{ $wiValue['name'] }}</option>
                                        </select>
                                        <span class="invalid-feedback d-block" id="error_stock_type" role="alert"></span>
                                    </div>
                                    <div class="col-12 col-md-3 col-lg-3 my-25 custom-input-group">
                                        <label class="form-label w-100" for="use_stock">Use Stock Project <b class="float-end">Available : {{ $wiValue['project_stock_qty'] }} {{ $wiValue['unit'] }}</b></label>
                                        <input type="number" max="{{$wiValue['project_usage'] + $wiValue['project_stock_qty']}}" class="form-control add-stock" name="use_project_stock[]" id="use_stock" placeholder="Use Stock" value="{{ $wiValue['project_usage'] }}">
                                        <span class="invalid-feedback d-block" id="error_no_of_inverter" role="alert"></span>

                                    </div>
                                    <div class="col-12 col-md-3 col-lg-3 my-25 custom-input-group">
                                        <label class="form-label w-100" for="use_stock">Use Stock Installer <b class="float-end">Available : {{ $wiValue['installer_stock_qty'] }} {{ $wiValue['unit'] }}</b></label>
                                        <input type="number" max="{{$wiValue['installer_usage'] +$wiValue['installer_stock_qty']}}" class="form-control add-stock" name="use_installer_stock[]" value="{{ $wiValue['installer_usage'] }}" id="use_stock" placeholder="Use Stock">
                                        <span class="invalid-feedback d-block" id="error_no_of_inverter" role="alert"></span>

                                    </div>
                                    <!-- <div class="col-12 col-md-1 col-lg-1 my-25 mt-2 custom-input-group add-item-buttons text-end">
                                        <button class="badge badge-light-danger border-0 add-new-items m-0" data-key="0" type="button"><i class="fas fa-trash"></i></button>
                                    </div> -->
                                </div>
                            </div>
                            @endforeach
                            <div class="row my-50">
                                <hr>
                                <div class="col-12">
                                    <button class="btn btn-sm btn-secondary btn-prev"><i data-feather='arrow-left'></i> Previous</button>
                                    <button class="btn btn-sm btn-primary float-end btn-next">Next <i data-feather='arrow-right'></i></button>
                                </div>
                            </div>
                        </div>
                        @php $j++; @endphp
                        @endforeach
                        <!-- Earthing Details -->
                        <div id="remark" class="content">
                            <div class="row g-1 pt-2" id="life_remark">
                                <div class="col-12 col-md-6 col-lg-4 my-25 custom-input-group">
                                    <label class="form-label" for="dc_side_earthing">DC Side Earthing</label>
                                    <input type="text" class="form-control text-uppercase" name="dc_side_earthing" id="dc_side_earthing" placeholder="DC Side Earthing" value="{{ (isset($installation) && isset($installation->dc_side)) ? $installation->dc_side : '0.53' }}">
                                </div>

                                <div class="col-12 col-md-6 col-lg-4 my-25 custom-input-group">
                                    <label class="form-label" for="ac_side_earthing">AC Side Earthing</label>
                                    <input type="text" class="form-control text-uppercase" name="ac_side_earthing" id="ac_side_earthing" placeholder="AC Side Earthing" value="{{ (isset($installation) && isset($installation->ac_side)) ? $installation->ac_side : '0.52' }}">
                                </div>

                                <div class="col-12 col-md-6 col-lg-4 my-25 custom-input-group">
                                    <label class="form-label" for="la_earthing">LA Earthing</label>
                                    <input type="text" class="form-control text-uppercase" name="la_earthing" id="la_earthing" placeholder="LA Earthing" value="{{ (isset($installation) && isset($installation->la_earthing)) ? $installation->la_earthing : '0.54' }}">
                                </div>

                                <div class="col-12 col-md-6 col-lg-6 my-25 custom-input-group">
                                    <label class="form-label" for="phase_to_earth">Phase to Earth</label>
                                    <input type="text" class="form-control text-uppercase" name="phase_to_earth" id="phase_to_earth" placeholder="Phase to Earth" value="{{ (isset($installation) && isset($installation->phase_to_earth)) ? $installation->phase_to_earth : '280' }}">
                                </div>

                                <div class="col-12 col-md-6 col-lg-6 my-25 custom-input-group">
                                    <label class="form-label" for="phase_to_phase">Phase to Phase</label>
                                    <input type="text" class="form-control text-uppercase" name="phase_to_phase" id="phase_to_phase" placeholder="Phase to Phase" value="{{ (isset($installation) && isset($installation->phase_to_phase)) ? $installation->phase_to_phase : '270' }}">
                                </div>
                            </div>
                            <div class="row my-50">
                                <hr>
                                <div class="col-12">
                                    <button class="btn btn-sm btn-secondary btn-prev"><i data-feather='arrow-left'></i> Previous</button>
                                    <button class="btn btn-sm btn-primary float-end btn-next">Next <i data-feather='arrow-right'></i></button>
                                </div>
                            </div>
                        </div>
                        <!-- Document Details -->
                        <div id="document" class="content">
                            <div class="row g-1">
                                <div class="repeater col-lg-6 border-end">
                                    <div class="table-responsive">
                                        <table class="table form-table table-bordered" id="order_table">
                                            <thead>
                                                <tr>
                                                    <th width="4%">#</th>
                                                    <th width="90%" class="text-center">Panel Image <span class="text-danger">*</span></th>
                                                    <th width="6%" class="text-center">Action</th>
                                                </tr>
                                            </thead>
                                            @if(isset($installation) && isset($installation->penalImage) && count($installation->penalImage))
                                            <tbody data-repeater-list="panel" class="sub_data">
                                                @foreach($installation->penalImage as $key => $value)
                                                <tr data-repeater-item class="add-more">
                                                    <td>
                                                        <b class="sr_no">{{ $key+1 }}</b>
                                                    </td>
                                                    <td class="custom-input-group">
                                                        <input type="file" class="form-control panel-image d-none" name="file" accept="image/*">
                                                        @if($value->image != '')
                                                        <a href="{{asset('uploads/penal/'.$value->image)}}" download="panel" class="clone_img">
                                                            <button type="button" class="btn btn-sm mt-50 btn-outline-primary"><span>Download</span> <i data-feather="download" class="me-25"></i></button>
                                                        </a>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <button type="button" class="btn btn-sm text-danger data-repeater-delete variant-delete" data-id="{{$value->id}}" data-type="panel" data-repeater-delete>
                                                            <i data-feather='trash-2'></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                            @else
                                            <tbody data-repeater-list="panel" class="sub_data">
                                                <tr data-repeater-item class="add-more">
                                                    <td>
                                                        <b class="sr_no">1</b>
                                                    </td>
                                                    <td class="custom-input-group">
                                                        <input type="file" class="form-control" name="file" accept="image/*" required>
                                                    </td>
                                                    <td>
                                                        <button type="button" class="btn btn-sm text-danger data-repeater-delete remove-item" data-repeater-delete>
                                                            <i data-feather='trash-2'></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                            @endif
                                            <tfoot>
                                                <tr>
                                                    <td colspan="4" class="text-end">
                                                        <button class="badge badge-light-success border-0 me-0" type="button" data-repeater-create>
                                                            <i data-feather="plus" class="me-25"></i>
                                                            <span>Add More</span>
                                                        </button>
                                                    </td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                                <div class="repeater_doc col-lg-6">
                                    <div class="table-responsive">
                                        <table class="table form-table table-bordered" id="inverter_table">
                                            <thead>
                                                <tr>
                                                    <th width="4%">#</th>
                                                    <th width="90%" class="text-center">Inverter Image</th>
                                                    <th width="6%" class="text-center">Action</th>
                                                </tr>
                                            </thead>
                                            @if(isset($installation) && isset($installation->invaterImages) && count($installation->invaterImages))
                                            <tbody data-repeater-list="inverter" class="sub_datas">
                                                @foreach($installation->invaterImages as $key => $value)
                                                <tr data-repeater-item class="new-more">
                                                    <td>
                                                        <input type="hidden" name="document_id" value="{{ $value->id }}">
                                                        <b class="sr_nos">{{ $key+1 }}</b>
                                                    </td>
                                                    <td class="custom-input-group">
                                                        <input type="file" class="form-control inverter-image d-none" name="file" accept="image/*">
                                                        @if($value->image != '')
                                                        <a href="{{asset('uploads/invater/'.$value->image)}}" download="inverter" class="clone_img">
                                                            <button type="button" class="btn btn-sm mt-50 btn-outline-primary"><span>Download</span> <i data-feather="download" class="me-25"></i></button>
                                                        </a>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <button type="button" class="btn btn-sm text-danger data-repeater-delete inverter-delete" data-id="{{$value->id}}" data-type="inverter" data-repeater-delete>
                                                            <i data-feather='trash-2'></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                            @else
                                            <tbody data-repeater-list="inverter" class="sub_datas">
                                                <tr data-repeater-item class="new-more">
                                                    <td>
                                                        <b class="sr_nos">1</b>
                                                    </td>
                                                    <td class="custom-input-group">
                                                        <input type="file" class="form-control" name="file" accept="image/*">
                                                    </td>
                                                    <td>
                                                        <button type="button" class="btn btn-sm text-danger data-repeater-delete remove-inverter" data-repeater-delete>
                                                            <i data-feather='trash-2'></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                            @endif
                                            <tfoot>
                                                <tr>
                                                    <td colspan="4" class="text-end">
                                                        <button class="badge badge-light-success border-0 me-0" type="button" data-repeater-create>
                                                            <i data-feather="plus" class="me-25"></i>
                                                            <span>Add More</span>
                                                        </button>
                                                    </td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="row my-50">
                                <hr>
                                <div class="col-12">
                                    <button class="btn btn-sm btn-secondary btn-prev"><i data-feather='arrow-left'></i> Previous</button>
                                    <button class="btn btn-sm btn-success float-end btn-submit save" data-status="2" id="full_save">Full Save</button>
                                    <button class="btn btn-sm btn-warning float-end btn-submit save me-1" data-status="1" id="half_save">Half Save</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
@section('pagescript')
<script type="text/javascript">
    $(document).on('change', '.inverter-item', function() {
        var selectedOption = $(this).find('option:selected');
        var require = selectedOption.attr('data-require');
        var stock = selectedOption.attr('data-stock');
        var key = selectedOption.attr('data-key');
        $('#inv_required_' + key).html(require);
        $('#inv_available_' + key).html(stock);
        $('#no_of_inverter_' + key).attr('max', stock);
    });

    $(document).on('click', '.add-new-inverter', function() {
        var item_length = $('.inverter_append_items').length;
        var clone_row = $('#inverter_append_0').clone();
        clone_row.attr('id', 'inverter_append_' + item_length);
        clone_row.find('#append_box_0').attr('id', 'append_box_' + item_length);

        clone_row.find('#inv_required_0').text('0');
        clone_row.find('#inv_available_0').text('0');

        clone_row.find('#append_inverter_0').attr('id', 'append_inverter_' + item_length);
        clone_row.find('#inverter_table_0').attr('id', 'inverter_table_' + item_length);

        clone_row.find('#inv_required_0').attr('id', 'inv_required_' + item_length);
        clone_row.find('#inv_available_0').attr('id', 'inv_available_' + item_length);

        clone_row.find('.no_of_inverter').attr('data-key', item_length);

        clone_row.find('.inverter-item option').each(function() {
            $(this).attr('data-key', item_length);
        });

        clone_row.find('input, select').each(function() {
            var currentId = $(this).attr('id');
            var currentName = $(this).attr('name');
            if (currentId) {
                $(this).attr('id', currentId.replace('_0', '_' + item_length));
            }
            if (currentName) {
                $(this).attr('name', currentName.replace('[]', '[' + item_length + ']'));
            }
            $(this).val('');
        });

        clone_row.find('.append_inverter').empty();
        clone_row.find('.select2-container').remove();

        if (clone_row.find('.select2').data('select2')) {
            clone_row.find('.select2').select2('destroy');
        }

        clone_row.find('.select2').select2();

        // Add the Remove button
        clone_row.find('.add-inverter-buttons').html('<button class="badge badge-light-danger border-0 remove-new-panel m-0" type="button">- Remove</button>');

        $('#inverter_appends_0').append(clone_row);
    });


    $(document).on('keyup', '.no_of_inverter', function() {

        var key = $(this).attr('data-key');
        var max = parseInt($(this).attr('max'));
        var numberOfInverters = parseInt($(this).val());
        if (max >= numberOfInverters) {

            $('#append_inverter_' + key).empty();
            for (var i = 0; i < numberOfInverters; i++) {
                $('#inverter_table_' + key).removeClass('d-none');
                var inverterFields = `
    <tr>
        <td class="text-center">${i + 1}</td>
        <td class="custom-input-group">
            <input type="text" class="form-control text-uppercase" name="inverter_model_number[` + key + `][]" placeholder="Model Number" value="" required>
        </td>
        <td class="custom-input-group">
            <input type="text" class="form-control text-uppercase" name="inverter_sr_number[` + key + `][]" placeholder="Sr Number" value="" required>
        </td>
        <td class="custom-input-group">
            <input type="text" class="form-control text-uppercase" name="inverter_voltage[` + key + `][]" placeholder="Voltage" value="" required>
        </td>
    </tr>
`;
                $('#append_inverter_' + key).append(inverterFields);
                $('.make_of_inverter').select2({
                    placeholder: "-- Select --",
                    allowClear: false,
                    width: "100%",
                });
            }
        } else {
            $(this).val('');
            $('#append_inverter_' + key).empty();
        }
    });


    $(document).on('click', '.add-new-panel', function() {
        var item_length = $('.panel_append_items').length;
        var clone_row = $('#panel_append_0').clone();
        clone_row.attr('id', 'panel_append_' + item_length);
        clone_row.find('#append_box_0').attr('id', 'append_box_' + item_length);

        clone_row.find('#required_0').text('0');
        clone_row.find('#available_0').text('0');

        clone_row.find('#required_0').attr('id', 'required_' + item_length);
        clone_row.find('#available_0').attr('id', 'available_' + item_length);

        clone_row.find('.no_of_modules').attr('data-key', item_length);

        clone_row.find('.panel_item option').each(function() {
            $(this).attr('data-key', item_length);
        });

        clone_row.find('input, select').each(function() {
            var currentId = $(this).attr('id');
            var currentName = $(this).attr('name');
            if (currentId) {
                $(this).attr('id', currentId.replace('_0', '_' + item_length));
            }
            if (currentName) {
                $(this).attr('name', currentName.replace('[]', '[' + item_length + ']'));
            }
            $(this).val('');
        });

        clone_row.find('.panel_sr_module').empty();
        clone_row.find('.select2-container').remove();

        if (clone_row.find('.select2').data('select2')) {
            clone_row.find('.select2').select2('destroy');
        }

        clone_row.find('.select2').select2();

        // Add the Remove button
        clone_row.find('.add-panel-buttons').html('<button class="badge badge-light-danger border-0 remove-new-panel m-0" type="button">- Remove</button>');

        $('#panel_appends_0').append(clone_row);
    });

    // Remove the panel when the Remove button is clicked
    $(document).on('click', '.remove-new-panel', function() {
        $(this).closest('.row').remove();
    });

    $(document).on('change', '.panel_item', function() {
        var selectedOption = $(this).find('option:selected');
        var require = selectedOption.attr('data-require');
        var stock = selectedOption.attr('data-stock');
        var panelwatt = selectedOption.attr('data-panelwatt');
        var key = selectedOption.attr('data-key');
        $('#panel_watt_' + key).val(panelwatt);
        $('#required_' + key).html(require);
        $('#available_' + key).html(stock);
        $('#no_of_modules_' + key).attr('max', stock);
    });

    $(document).on('keyup', '.no_of_modules', function() {
        var key = $(this).attr('data-key');
        var watt = $('#panel_watt_' + key).val();
        var modules = parseInt($(this).val());
        var total_kw = (watt * modules) / 1000;
        $('#total_kw_' + key).val(total_kw);

        var max = parseInt($(this).attr('max'));
        var no_of_modules = parseInt($(this).val());
        if (max >= no_of_modules) {
            $('#append_box_' + key).addClass('border-top');
            var numberOfpanel = $(this).val();

            $('#append_box_' + key).empty();
            for (var i = 0; i < numberOfpanel; i++) {
                var fields = `<div class="col-12 col-md-6 col-lg-4 my-25 custom-input-group">
                                        <label class="form-label">Sr Number ${i + 1} <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control text-uppercase" name="panel_sr_module[` + key + `][]" placeholder="Sr Number ${i + 1}" required>
                                        </div>`;
                $('#append_box_' + key).append(fields);
            }
        } else {
            $(this).val('');
            $('#append_box_' + key).empty();
        }

    });

    $('.repeater').repeater({
        show: function() {
            var obj = $(this);
            var sr = $('.sr_no').length;
            obj.find('.sr_no').text(sr);

            if (feather) {
                feather.replace({
                    width: 14,
                    height: 14
                });
            }
            obj.find('.variant-delete').removeAttr('data-id');
            obj.find('.clone_img').last().remove();
            obj.find('.panel-image').attr('required', true);
            obj.find('.panel-image').removeClass('d-none');
            $(this).slideDown();


        },
        hide: function(deleteElement) {
            var obj = $(this);
            var len = obj.parent().parent().find('.remove-item').length;
            if (len != 0) {
                if (len > 1) {
                    $(this).slideUp(deleteElement);
                } else {
                    Swal.fire({
                        text: "Cannot delete first item",
                        icon: 'warning',
                        confirmButtonText: 'OK',
                    });
                }
            }
        }
    });

    $('.repeater_doc').repeater({
        show: function() {
            var obj = $(this);
            var sr = $('.sr_nos').length;
            obj.find('.sr_nos').text(sr);
            if (feather) {
                feather.replace({
                    width: 14,
                    height: 14
                });
            }
            obj.find('.inverter-delete').removeAttr('data-id');
            obj.find('.clone_img').last().remove();
            obj.find('.inverter-image').removeClass('d-none');
            $(this).slideDown();


        },
        hide: function(deleteElement) {
            var obj = $(this);
            var len = obj.parent().parent().find('.remove-inverter').length;
            if (len != 0) {
                if (len > 1) {
                    $(this).slideUp(deleteElement);
                } else {
                    Swal.fire({
                        text: "Cannot delete first item",
                        icon: 'warning',
                        confirmButtonText: 'OK',
                    });
                }
            }
        }
    });



    $(document).on('click', '.variant-delete', function() {
        if (($('.variant-delete').length) > 1) {
            var btn = $(this);
            var id = $(this).data('id');
            var type = $(this).data('type');
            if (id != undefined) {
                removeDocument(btn, id, type);
            } else {
                btn.parent().parent().remove();
            }
        } else {
            Swal.fire({
                text: "Cannot delete first item",
                icon: 'warning',
                confirmButtonText: 'OK',
            });
        }
    });

    function removeDocument(btn, id, type) {
        Swal.fire({
                title: "Are you sure?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: "Yes, Delete",
                customClass: {
                    confirmButton: 'btn btn-primary',
                    cancelButton: 'btn btn-outline-danger ms-1'
                },
                buttonsStyling: false
            })
            .then(function(result) {
                if (result.value) {
                    $.ajax({
                        type: "POST",
                        url: "{{route('installation-image')}}",
                        data: {
                            'id': id,
                            'type': type,
                            "_token": "{{ csrf_token() }}",
                        },
                        dataType: 'json',
                        cache: false,
                        success: function(response) {
                            if (response.status_code == 500) {
                                toastr.error(response.message, "Error");
                            } else {
                                toastr.success(response.message, "Success");
                                btn.parent().parent().slideUp();
                                setTimeout(function() {
                                    btn.parent().parent().remove();
                                }, 500);
                            }
                        }
                    });
                }
            });
    }

    $(document).ready(function() {



    });

    $(document).on('click', ".btn-next", function() {
        if ($("#form").valid()) {
            return true;
        } else {
            return false;
        }
    });

    $("#form").validate({
        rules: {
            penal_company_id: {
                required: true
            },
            panel_model_number: {
                required: true
            },
            penal_type_id: {
                required: true,
            },
            penal_watt_id: {
                required: true
            },
            no_of_modules: {
                required: true
            },
            inverter_type: {
                required: true
            },
            no_of_inverter: {
                required: true
            },
            'sr_module[]': {
                required: true
            }
            // dc: {
            //     required: true
            // },
            // ac: {
            //     required: true
            // },
            // la: {
            //     required: true
            // },
            // earthing: {
            //     required: true
            // },
        },
        messages: {
            penal_company_id: {
                required: "Select make of solar PV Module"
            },
            penal_type_id: {
                required: "Select type of PV Module"
            },
            penal_watt_id: {
                required: "Select capacity of Solar Module"
            },
            panel_model_number: {
                required: "Enter model number"
            },
            no_of_modules: {
                required: "Enter no of modules",
            },
            inverter_type: {
                required: "Select inverter type",
            },
            no_of_inverter: {
                required: "Enter no of inverter",
            },
            'sr_module[]': {
                required: "Enter sr number",
            }
            // dc: {
            //     required: "Enter dc",
            // },
            // ac: {
            //     required: "Enter ac",
            // },
            // la: {
            //     required: "Enter la",
            // },
            // earthing: {
            //     required: "Enter earthing",
            // },
        },
        errorElement: "p",
        errorClass: "text-danger mb-0",
        highlight: function(element) {
            $(element).addClass('has-error');
        },
        unhighlight: function(element) {
            $(element).removeClass('has-error');
        },
        errorPlacement: function(error, element) {
            $(element).closest('.custom-input-group').append(error);
        }
    });

    $(document).on('click', '.save', function() {
        $('#status').remove();
        var status = $(this).data('status');
        var btnId, btnText;
        // Initially assume the form is valid

        if (status == 1) {
            btnId = 'half_save';
            btnText = 'Half Save';
            var isValid = true;
        } else {
            btnId = 'full_save';
            btnText = 'Full Save';

            // Validate the main required fields
            var isValid = ($("#penal_company_id").val() != "" &&
                $("#panel_model_number").val() != "" &&
                $("#penal_type_id").val() != "" &&
                $("#penal_watt_id").val() != "" &&
                $("#no_of_modules").val() != "" &&
                $("#inverter_type").val() != "" &&
                $("#no_of_inverter").val() != "" &&
                $("#form").valid());

            if (isValid) {
                $("input[name='sr_module[]']").each(function() {
                    if ($(this).val() === "") {
                        isValid = false;
                        return false;
                    }
                });

                if (isValid) {
                    $("input[name='inverter_model_number[]']").each(function() {
                        if ($(this).val() === "") {
                            isValid = false;
                            return false;
                        }
                    });
                    if (isValid) {
                        $("input[name='inverter_kw[]']").each(function() {
                            if ($(this).val() === "") {
                                isValid = false;
                                return false;
                            }
                        });
                        if (isValid) {
                            $("input[name='sr_number[]']").each(function() {
                                if ($(this).val() === "") {
                                    isValid = false;
                                    return false;
                                }
                            });
                            if (isValid) {
                                $("input[name='voltage[]']").each(function() {
                                    if ($(this).val() === "") {
                                        isValid = false;
                                        return false;
                                    }
                                });
                            }
                        }
                    }
                }
            }
        }

        // If the form is valid, proceed with the AJAX request
        if (isValid) {
            $("<input />").attr("type", "hidden")
                .attr("id", "status")
                .attr("name", "status")
                .attr("value", status)
                .appendTo("#form");

            var formData = new FormData($("#form")[0]);



            $.ajax({
                type: "POST",
                url: "{{ route('installation.store') }}",
                data: formData,
                dataType: 'json',
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: function() {
                    $('#' + btnId).html(`<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Wait`);
                    $('#' + btnId).attr('disabled', true);
                },
                success: function(response) {
                    $('#' + btnId).html(btnText);
                    $('#' + btnId).attr('disabled', false);
                    if (response.status_code == 500) {
                        toastr.error(response.message, "Error");
                    } else if (response.status_code == 403) {
                        toastr.warning(response.message, "Warning");
                    } else if (response.status_code == 201) {
                        $.each(response.errors, function(key, value) {
                            $('#error_' + key).html('<p class="text-danger mb-0">' + value + '</p>');
                        });
                        toastr.warning(response.message, "Warning");
                    } else {
                         $('#form')[0].reset();
                        toastr.success(response.message, "Success");
                         setTimeout(function() {
                             location.href = response.data;
                         }, 800);
                    }
                }
            });
        } else {
            toastr.warning('* Please input proper data.', "Warning");
            return false;
        }
    });

    function validateArrayField(fieldName) {
        var isValid = true;
        $("input[name='" + fieldName + "']").each(function() {
            if ($(this).val() === "") {
                isValid = false;
                return false; // Break loop if an empty value is found
            }
        });
        return isValid;
    }

    $('.select2').select2();
    $('.select2').on('change', function() {
        var element = $(this).attr('name');
        $('#form').validate().showErrors({
            [element]: ''
        });
    });

    $('.add-stock').on('keyup', function() {
        var key = $(this).attr('data-key');
        var max = parseInt($(this).attr('max'));
        var numberOfInverters = parseInt($(this).val());
        if (max < numberOfInverters) {
            $(this).val('');
        }
    });
</script>
@endsection