@extends('layouts.app')
@section('title', 'Sales Quatation')
@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-lite.min.css" />
@endsection
@section('content')
<div class="row">
    <div class="col-12">
        @if((isset($sales_quatation) && isset($sales_quatation->id)))
        <h4 class="card-title mb-1">{{ __('message.Edit Sales Quatation') }}</h4>
        <input type="hidden" id="operation_type" value="edit">
        @else
        <input type="hidden" id="operation_type" value="add">
        <h4 class="card-title mb-1">{{ __('message.Add Sales Quatation') }}</h4>
        @endif
    </div>
    <div class="col-12">
        <div class="card p-1">
            <div class="form-group radioeffect">
                <input name="form_type" id="radio1" value="trading" class="form-check-input form_type" type="radio" {{ isset($sales_quatation) && $sales_quatation->form_type == 'trading' ? 'checked' : '' }} checked>
                <label for="radio1" class="form-check-label radGroup1">{{ __('message.Trading') }} &nbsp; &nbsp;</label>
                <input name="form_type" id="radio2" value="resident" class="form-check-input form_type" type="radio" {{ isset($sales_quatation) && $sales_quatation->form_type == 'resident' ? 'checked' : '' }}>
                <label for="radio2" class="form-check-label radGroup1">{{ __('message.Resident With Subsidy') }}&nbsp; &nbsp;</label>
                <input name="form_type" id="radio3" value="roof" class="form-check-input form_type" type="radio" {{ isset($sales_quatation) && $sales_quatation->form_type == 'roof' ? 'checked' : '' }}>
                <label for="radio3" class="form-check-label radGroup1">{{ __('message.Solar RoofTop') }}</label>
            </div>
            <hr>
            <form id="trading_form" class="invoice-repeater" action="javascript:void(0)" method="POST">
                @csrf
                <div class="row">
                    <input type="hidden" id="form_type" name="form_type" value="trading">
                    @if((isset($sales_quatation) && isset($sales_quatation->id)))
                    <input type="hidden" id="sales_quatation_id" name="sales_quatation_id" value="{{ $sales_quatation->id }}">
                    <script src="{{asset('app-assets/vendors/js/jquery/jquery.min.js')}}"></script>
                    <script>
                        $(document).ready(function() {
                            var Value = $('.form_type:checked').val();
                            if (Value == 'trading') {
                                $("#res_form").addClass('d-none');
                                $("#roofdataform").addClass('d-none');
                                $("#trading_form").removeClass('d-none');
                            } else if (Value == 'resident') {
                                $("#trading_form").addClass('d-none');
                                $("#res_form").removeClass('d-none');
                                $("#roofdataform").addClass('d-none');
                            } else if (Value == 'roof') {
                                $("#res_form").addClass('d-none');
                                $("#trading_form").addClass('d-none');
                                $("#roofdataform").removeClass('d-none');
                                roofTableCalculation();
                            }
                        });
                    </script>
                    @endif
                    <div class="col-12 col-md-6 col-lg-4 mb-1 custom-input-group">
                        <label class="form-label" for="lead_master_id">{{ __('message.Lead') }} <span class="text-danger">*</span></label>
                        <select class="form-control anlayst form-select select2 custom-select2" name="lead_master_id" id="lead_master_id">
                            <option selected disabled value="">{{ __('message.-- Select --') }}</option>
                            @foreach($lead_complete as $value)
                            <option value="{{$value->id}}" data-mobile="{{$value->mobile}}" data-name="{{$value->name}}" data-reference="{{$value->reference}}" data-agent_sales_person_id="{{$value->agent_sales_person_id}}" {{ (isset($sales_quatation) && ($sales_quatation->lead_master_id == $value->id) ? 'selected' : '')}}>{{$value->name}} - {{$value->mobile}}</option>
                            @endforeach
                        </select>
                        <span class="invalid-feedback d-block" id="error_lead_master_id" role="alert"></span>
                    </div>
                    <div class="col-12 col-md-6 col-lg-4 mb-1 custom-input-group">
                        <label class="form-label" for="mobile">{{ __('message.Mobile') }} <span class="text-danger">*</span></label>
                        <input type="number" maxlength="10" class="form-control" name="mobile" id="mobile" placeholder="{{ __('message.Mobile No.') }}" value="{{ ((isset($sales_quatation) && isset($sales_quatation->mobile)) ? $sales_quatation->mobile : '')  }}">
                        <span class="invalid-feedback d-block" id="error_mobile" role="alert"></span>
                    </div>
                    <div class="col-12 col-md-6 col-lg-4 mb-1 custom-input-group">
                        <label class="form-label" for="name">{{ __('message.Name') }} <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" id="name" placeholder="{{ __('message.Name') }}" value="{{ ((isset($sales_quatation) && isset($sales_quatation->name)) ? $sales_quatation->name : '')  }}" oninput="this.value = this.value.toUpperCase()">
                        <span class="invalid-feedback d-block" id="error_name" role="alert"></span>
                    </div>
                    <div class="col-12 col-md-6 col-lg-4 mb-1 custom-input-group">
                        <label class="form-label" for="address">{{ __('message.Bill to Address') }} <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="address" id="address" placeholder="{{ __('message.Bill to Address') }}" value="{{ ((isset($sales_quatation) && isset($sales_quatation->address)) ? $sales_quatation->address : '')  }}">
                        <span class="invalid-feedback d-block" id="error_address" role="alert"></span>
                    </div>
                    <div class="col-12 col-md-6 col-lg-4 mb-1 custom-input-group">
                        <label class="form-label" for="ship_to">{{ __('message.Ship To') }} <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="ship_to" id="ship_to" placeholder="{{ __('message.Ship To') }}" value="{{ ((isset($sales_quatation) && isset($sales_quatation->ship_to)) ? $sales_quatation->ship_to : '')  }}">
                        <span class="invalid-feedback d-block" id="error_ship_to" role="alert"></span>
                    </div>
                    <div class="col-12 col-md-6 col-lg-4 mb-1 custom-input-group">
                        <label class="form-label" for="gst_no">{{ __('message.GST Number') }} <span class="text-danger"></span></label>
                        <input type="text" class="form-control" name="gst_no" id="gst_no" oninput="this.value = this.value.toUpperCase()" placeholder="{{ __('message.GST Number') }}" value="{{ ((isset($sales_quatation) && isset($sales_quatation->gst_no)) ? $sales_quatation->gst_no : '')  }}">
                        <span class="invalid-feedback d-block" id="error_gst_no" role="alert"></span>
                    </div>
                </div>
                @if(isset($meta) && $meta != '')
                <div data-repeater-list="invoice" class="col-12">
                    @foreach($meta as $record)
                    <div data-repeater-item>
                        <div class="row">
                            <div class="col-12 col-md-2 col-lg-2 mb-1 custom-input-group">
                                <label class="form-label" for="item_id">Type <span class="text-danger">*</span></label>
                                <select class="form-select custom-select2 type" name="type" required>
                                    <option value="Item" {{ (isset($meta) && ($record->type == 'Item') ? 'selected' : '')}}>BOS</option>
                                    <option value="ItemGroup" {{ (isset($meta) && ($record->type == 'ItemGroup') ? 'selected' : '')}}>Panel/Inverter</option>
                                </select>
                            </div>

                            <div class="col-12 col-md-2 col-lg-3 mb-1 custom-input-group type-item {{ (isset($meta) && ($record->type != 'Item') ? 'd-none' : '')}}">
                                <label class="form-label" for="item_id">{{ __('message.Item') }} <span class="text-danger">*</span></label>

                                <select class="form-control item_id" name="item_id" id="item_id" required>
                                    <option selected disabled value="">{{ __('message.-- Select --') }}</option>
                                    @foreach($product as $value)
                                    <option value="{{$value->id}}" data-gst_rate="{{$value->gst_rate}}" {{ (isset($meta) && ($record->item_id == $value->id) ? 'selected' : '')}}>{{$value->name}}</option>
                                    @endforeach
                                </select>
                                <span class="invalid-feedback d-block" id="error_item_id" role="alert"></span>
                            </div>
                            <div class="col-12 col-md-2 col-lg-3 mb-1 custom-input-group  type-item-group {{ (isset($meta) && ($record->type == 'Item') ? 'd-none' : '')}} ">
                                <label class="form-label" for="item_id">{{ __('message.Item') }} <span class="text-danger">*</span></label>
                                <select class="form-select item_group_id custom-select2" name="item_group_id" required>
                                    <option value="" selected disabled>-- Select --</option>
                                    @foreach ($itemGroup as $k => $v)
                                    <option value="{{ $v['id'] }}" data-gst_rate="{{ $v['gst_rate'] }}" {{ (isset($meta) && ($record->item_group_id == $v->id) ? 'selected' : '')}}>{{ getItemGropName($v,0) }}</option>
                                    @endforeach
                                </select>
                                <span class="invalid-feedback d-block" id="error_item_id" role="alert"></span>
                            </div>
                            <div class="col-12 col-md-6 col-lg-1 mb-1 custom-input-group">
                                <label class="form-label" for="nos">{{ __('message.Nos') }} <span class="text-danger">*</span></label>
                                <input type="hidden" id="sales_quatation_meta_id" name="sales_quatation_meta_id" value="{{ ((isset($meta) && isset($record->id)) ? $record->id : '')  }}">
                                <input type="number" class="form-control nos" name="nos" id="nos" placeholder="{{ __('message.Nos') }}" value="{{ ((isset($meta) && isset($record->nos)) ? $record->nos : '')  }}" required>
                                <span class="invalid-feedback d-block" id="error_nos" role="alert"></span>
                            </div>
                            <div class="col-12 col-md-6 col-lg-2 mb-1 custom-input-group">
                                <label class="form-label" for="rate">{{ __('message.Rate') }} <span class="text-danger">*</span></label>
                                <input type="number" class="form-control rate" name="rate" id="rate" placeholder="{{ __('message.Rate') }}" value="{{ ((isset($meta) && isset($record->rate)) ? $record->rate : '')  }}" required>
                                <span class="invalid-feedback d-block" id="error_rate" role="alert"></span>
                            </div>
                            <div class="col-12 col-md-6 col-lg-1 mb-1 custom-input-group">
                                <label class="form-label" for="item_gst">{{ __('message.GST') }} (%)<span class="text-danger">*</span></label>
                                <input type="number" readonly class="form-control item_gst" name="item_gst" id="item_gst" placeholder="{{ __('message.GST') }}" value="{{ ((isset($meta) && isset($record->item_gst)) ? $record->item_gst : '')  }}">
                                <span class="invalid-feedback d-block" id="error_item_gst" role="alert"></span>
                            </div>
                            <div class="col-12 col-md-6 col-lg-2 mb-1 custom-input-group">
                                <label class="form-label" for="item_gst">Total Taxable<span class="text-danger">*</span></label>
                                <input type="number" readonly class="form-control total_taxable" name="total_taxable" id="total_taxable" placeholder="Taxable" value="0">
                                <span class="invalid-feedback d-block" id="error_total_taxable" role="alert"></span>
                            </div>
                            <div class="col-12 col-lg-1 d-flex align-items-center">
                                <button class="btn btn-outline-danger btn-sm text-nowrap px-1 mt-2 float-end data-repeater-delete remove-item" data-id="{{$record->id}}" data-repeater-delete type="button">
                                    <i data-feather="x"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div data-repeater-list="invoice" class="col-12">
                    <div data-repeater-item>
                        <div class="row">
                            <div class="col-12 col-md-2 col-lg-2 mb-1 custom-input-group">
                                <label class="form-label" for="item_id">Type <span class="text-danger">*</span></label>

                                <select class="form-select custom-select2 type" name="type" required>
                                    <option value="Item">BOS</option>
                                    <option value="ItemGroup">Panel/Inverter</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-2 col-lg-3 mb-1 custom-input-group type-item">
                                <label class="form-label" for="item_id">{{ __('message.Item') }} <span class="text-danger">*</span></label>

                                <select class="form-control item_id" name="item_id" id="item_id" required>
                                    <option selected disabled value="">{{ __('message.-- Select --') }}</option>
                                    @foreach($product as $value)
                                    <option value="{{$value->id}}" data-gst_rate="{{$value->gst_rate}}" {{ (isset($sales_quatation) && ($sales_quatation->item_id == $value->id) ? 'selected' : '')}}>{{$value->name}}</option>
                                    @endforeach
                                </select>
                                <span class="invalid-feedback d-block" id="error_item_id" role="alert"></span>
                            </div>
                            <div class="col-12 col-md-2 col-lg-3 mb-1 custom-input-group  type-item-group d-none ">
                                <label class="form-label" for="item_id">{{ __('message.Item') }} <span class="text-danger">*</span></label>
                                <select class="form-select item_group_id custom-select2" name="item_group_id" required>
                                    <option value="" selected disabled>-- Select --</option>
                                    @foreach ($itemGroup as $k => $v)
                                    <option value="{{ $v['id'] }}" data-gst_rate="{{ $v['gst_rate'] }}">{{ getItemGropName($v,0) }}</option>
                                    @endforeach
                                </select>
                                <span class="invalid-feedback d-block" id="error_item_id" role="alert"></span>
                            </div>

                            <div class="col-12 col-md-6 col-lg-1 mb-1 custom-input-group">
                                <label class="form-label" for="nos">{{ __('message.Nos') }} <span class="text-danger">*</span></label>
                                <input type="number" class="form-control nos" name="nos" id="nos" placeholder="{{ __('message.Nos') }}" value="" required>
                                <span class="invalid-feedback d-block" id="error_nos" role="alert"></span>
                            </div>
                            <div class="col-12 col-md-6 col-lg-2 mb-1 custom-input-group">
                                <label class="form-label" for="rate">{{ __('message.Rate') }} <span class="text-danger">*</span></label>
                                <input type="number" class="form-control rate" name="rate" id="rate" placeholder="{{ __('message.Rate') }}" value="" required>
                                <span class="invalid-feedback d-block" id="error_rate" role="alert"></span>
                            </div>
                            <div class="col-12 col-md-6 col-lg-1 mb-1 custom-input-group">
                                <label class="form-label" for="item_gst">{{ __('message.GST') }} (%)<span class="text-danger">*</span></label>
                                <input type="number" readonly class="form-control item_gst" name="item_gst" id="item_gst" placeholder="{{ __('message.GST') }}" value="">
                                <span class="invalid-feedback d-block" id="error_item_gst" role="alert"></span>
                            </div>

                            <div class="col-12 col-md-6 col-lg-2 mb-1 custom-input-group">
                                <label class="form-label" for="item_gst">Total Taxable<span class="text-danger">*</span></label>
                                <input type="number" readonly class="form-control total_taxable" name="total_taxable" id="total_taxable" placeholder="Taxable" value="0">
                                <span class="invalid-feedback d-block" id="error_total_taxable" role="alert"></span>
                            </div>

                            <div class="col-12 col-lg-1 d-flex align-items-center">
                                <button class="btn btn-outline-danger btn-sm text-nowrap px-1 mt-1 float-end data-repeater-delete remove-item" data-id="{{$value->id}}" data-repeater-delete type="button">
                                    <i data-feather="x"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                <div class="row">
                    <div class="col-12 col-md-6 col-lg-4 mb-1 custom-input-group">
                        <button class="btn btn-sm btn-icon btn-primary" type="button" data-repeater-create>
                            <i class="fa fa-plus me-25"></i> <span>{{ __('message.Add New') }}</span>
                        </button>
                    </div>
                    <div class="col-12 col-md-6 col-lg-4 mb-1 custom-input-group radioeffect">
                        <label class="form-label mb-1">{{ __('message.GST') }}<span class="text-danger">*</span></label><br>
                        <input name="trading_gst" id="trading_gst_1" class="form-check-input trading_gst" type="radio" data-class="div1" value="Including" {{ (!isset($sales_quatation) || $sales_quatation->gst == 'Including') ? 'checked' : ''}}>
                        <label for="trading_gst_1" class="form-check-label radGroup1">{{ __('message.Including') }} &nbsp; &nbsp;</label>
                        <input name="trading_gst" id="trading_gst_2" class="form-check-input trading_gst" type="radio" data-class="div2" value="Extra" {{ isset($sales_quatation) && ($sales_quatation->gst == 'Extra') ? 'checked' : '' }}>
                        <label for="trading_gst_2" class="form-check-label radGroup1">{{ __('message.Extra') }}</label>
                    </div>
                    <div class="col-12 col-md-6 col-lg-4 mb-1 custom-input-group">
                        <label class="form-label" for="trading_total_amount">Total Amount <span class="text-danger"></span></label>
                        <input type="text" class="form-control" name="trading_total_amount" id="trading_total_amount" placeholder="Total Amount" value="{{ ((isset($sales_quatation) && isset($sales_quatation->total_amount)) ? $sales_quatation->total_amount : '')  }}" readonly>
                        <span class="invalid-feedback d-block" id="error_trading_total_amount" role="alert"></span>
                    </div>
                    <div class="col-12 col-md-6 col-lg-4 mb-1 custom-input-group">
                        <label class="form-label" for="reference">{{ __('message.Reference') }} <span class="text-danger"></span></label>
                        <input type="text" class="form-control" name="reference" id="reference" placeholder="{{ __('message.Reference') }}" value="{{ ((isset($sales_quatation) && isset($sales_quatation->reference)) ? $sales_quatation->reference : '')  }}" oninput="this.value = this.value.toUpperCase()">
                        <span class="invalid-feedback d-block" id="error_reference" role="alert"></span>
                    </div>
                    <div class="col-12 col-md-6 col-lg-4 mb-1 custom-input-group">
                        <label class="form-label" for="agent_sales_person_id">Agent Sales Person<span class="text-danger">*</span></label>
                        <select class="form-control form-select select2 custom-select2 agent_sales_person_id" name="agent_sales_person_id" id="agent_sales_person_id">
                            <option selected disabled value="">-- Select --</option>
                            @foreach($agentSalesPerson as $value)
                            <option value="{{$value->id}}" {{ (isset($sales_quatation) && $sales_quatation->agent_sales_person_id == $value->id ) ? 'selected' : '' }}>{{ $value->name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-6 col-lg-4 mb-1 custom-input-group">
                        <label class="form-label" for="trading_bank_id">Bank<span class="text-danger">*</span></label>
                        <select class="form-control form-select select2 custom-select2 trading_bank_id" name="trading_bank_id" id="trading_bank_id">
                            <option selected disabled value="">-- Select --</option>
                            @foreach($bank as $value)
                            <option value="{{$value->id}}" {{ (isset($sales_quatation) && $sales_quatation->bank_id == $value->id ) ? 'selected' : ($value->default == 1 ? 'selected' : '') }}>{{ $value->name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-12 mt-2">
                    <button type="submit" class="btn btn-sm btn-primary float-end save_tra">{{ __('message.Submit') }}</button>
                    <a role="botton" class="btn btn-sm btn-primary float-end mx-1" href="{{route('sales-quatation.index')}}">{{ __('message.Cancel') }}</a>
                </div>
            </form>
            <form id="res_form" action="javascript:void(0)" method="POST" class="d-none">
                @csrf
                <div class="row">
                    <input type="hidden" id="form_type" name="form_type" value="resident">
                    @if((isset($sales_quatation) && isset($sales_quatation->id)))
                    <input type="hidden" id="sales_quatation_id" name="sales_quatation_id" value="{{ $sales_quatation->id }}">
                    <script src="{{asset('app-assets/vendors/js/jquery/jquery.min.js')}}"></script>
                    <script>
                        $(document).ready(function() {
                            var Value = $('.form_type:checked').val();
                            if (Value == 'trading') {
                                $("#res_form").addClass('d-none');
                                $("#roofdataform").addClass('d-none');
                                $("#trading_form").removeClass('d-none');
                            } else if (Value == 'resident') {
                                $("#trading_form").addClass('d-none');
                                $("#res_form").removeClass('d-none');
                                $("#roofdataform").addClass('d-none');
                            } else if (Value == 'roof') {
                                $("#res_form").addClass('d-none');
                                $("#trading_form").addClass('d-none');
                                $("#roofdataform").removeClass('d-none');
                            }
                        });
                    </script>
                    @endif
                    <div class="col-12 col-md-6 col-lg-4 mb-1 custom-input-group">
                        <label class="form-label" for="res_lead_master_id">{{ __('message.Lead') }} <span class="text-danger">*</span></label>
                        <select class="form-control anlayst form-select select2 custom-select2" name="res_lead_master_id" id="res_lead_master_id">
                            <option selected disabled value="">{{ __('message.-- Select --') }}</option>
                            @foreach($lead_complete as $value)
                            <option value="{{$value->id}}" data-mobile="{{$value->mobile}}" data-name="{{$value->name}}" data-reference="{{$value->reference}}" data-agent_sales_person_id="{{$value->agent_sales_person_id}}" {{ (isset($sales_quatation) && ($sales_quatation->lead_master_id == $value->id) ? 'selected' : '')}}>{{$value->name}} - {{$value->mobile}}</option>
                            @endforeach
                        </select>
                        <span class="invalid-feedback d-block" id="error_res_lead_master_id" role="alert"></span>
                    </div>
                    <div class="col-12 col-md-6 col-lg-4 mb-1 custom-input-group">
                        <label class="form-label" for="res_mobile">{{ __('message.Mobile') }} <span class="text-danger">*</span></label>
                        <input type="number" maxlength="10" class="form-control" name="res_mobile" id="res_mobile" placeholder="{{ __('message.Mobile No.') }}" value="{{ ((isset($sales_quatation) && isset($sales_quatation->mobile)) ? $sales_quatation->mobile : '')  }}">
                        <span class="invalid-feedback d-block" id="error_res_mobile" role="alert"></span>
                    </div>
                    <div class="col-12 col-md-6 col-lg-4 mb-1 custom-input-group">
                        <label class="form-label" for="res_name">{{ __('message.Name') }} <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="res_name" id="res_name" placeholder="{{ __('message.Name') }}" value="{{ ((isset($sales_quatation) && isset($sales_quatation->name)) ? $sales_quatation->name : '')  }}" oninput="this.value = this.value.toUpperCase()">
                        <span class="invalid-feedback d-block" id="error_res_name" role="alert"></span>
                    </div>
                    <div class="col-12 col-md-6 col-lg-4 mb-1 custom-input-group">
                        <label class="form-label" for="res_address">{{ __('message.Bill to Address') }} <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="res_address" id="res_address" placeholder="{{ __('message.Bill to Address') }}" value="{{ ((isset($sales_quatation) && isset($sales_quatation->address)) ? $sales_quatation->address : '')  }}">
                        <span class="invalid-feedback d-block" id="error_res_address" role="alert"></span>
                    </div>
                    <div class="col-12 col-md-6 col-lg-4 mb-1 custom-input-group">
                        <label class="form-label" for="res_penal_company_id">{{ __('message.Panel Company Name') }} <span class="text-danger">*</span></label>
                        <select class="form-control anlayst" name="res_penal_company_id[]" id="res_penal_company_id" multiple>
                            <option disabled value="">{{ __('message.-- Select --') }}</option>
                            @foreach($penal_company as $value)
                            @php
                            $selected = (isset($sales_quatation) && in_array($value->id, explode(',', $sales_quatation->penal_company_id))) ? 'selected' : '';
                            @endphp
                            <option value="{{$value->id}}" {{$selected}}>{{$value->name}}</option>
                            @endforeach
                        </select>
                        <span class="invalid-feedback d-block" id="error_res_penal_company_id" role="alert"></span>
                    </div>
                    <div class="col-12 col-md-6 col-lg-4 mb-1 custom-input-group">
                        <label class="form-label" for="res_penal_type_id">{{ __('message.Panel Type') }} <span class="text-danger">*</span></label>
                        <select class="form-control anlayst select2 custom-select2" name="res_penal_type_id" id="res_penal_type_id">
                            <option selected disabled value="">{{ __('message.-- Select --') }}</option>
                            @foreach($penal_type as $value)
                            <option value="{{$value->id}}" {{ (isset($sales_quatation) && ($sales_quatation->penal_type_id == $value->id) ? 'selected' : '')}}>{{$value->name}}</option>
                            @endforeach
                        </select>
                        <span class="invalid-feedback d-block" id="error_res_penal_type_id" role="alert"></span>
                    </div>
                    <div class="col-12 col-md-6 col-lg-4 mb-1 custom-input-group">
                        <label class="form-label" for="res_penal_watt_id">{{ __('message.Panel Watt') }} <span class="text-danger">*</span></label>
                        <select class="form-control anlayst select2 custom-select2" name="res_penal_watt_id" id="res_penal_watt_id">
                            <option selected disabled value="">{{ __('message.-- Select --') }}</option>
                            @foreach($penal_watt as $value)
                            <option value="{{$value->id}}" {{ (isset($sales_quatation) && ($sales_quatation->penal_watt_id == $value->id) ? 'selected' : '')}}>{{$value->name}}</option>
                            @endforeach
                        </select>
                        <span class="invalid-feedback d-block" id="error_res_penal_watt_id" role="alert"></span>
                    </div>
                    <div class="col-12 col-md-6 col-lg-4 mb-1 custom-input-group">
                        <label class="form-label" for="res_penal_nos">{{ __('message.Panel Nos') }} <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="res_penal_nos" id="res_penal_nos" placeholder="{{ __('message.Panel Nos') }}" value="{{ ((isset($sales_quatation) && isset($sales_quatation->penal_nos)) ? $sales_quatation->penal_nos : '')  }}">
                        <span class="invalid-feedback d-block" id="error_res_penal_nos" role="alert"></span>
                    </div>
                    <div class="col-12 col-md-6 col-lg-4 mb-1 custom-input-group">
                        <label class="form-label" for="res_pv_capacity_kw">{{ __('message.PV Capacity Kw') }} <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="res_pv_capacity_kw" id="res_pv_capacity_kw" placeholder="{{ __('message.PV Capacity Kw') }}" value="{{ ((isset($sales_quatation) && isset($sales_quatation->pv_capacity_kw)) ? $sales_quatation->pv_capacity_kw : '')  }}" readonly>
                        <span class="invalid-feedback d-block" id="error_res_pv_capacity_kw" role="alert"></span>
                    </div>
                    <div class="col-12 col-md-6 col-lg-4 mb-1 custom-input-group">
                        <label class="form-label" for="res_inveter_company_id">{{ __('message.Inveter Company Name') }} <span class="text-danger">*</span></label>
                        <select class="form-control anlayst" name="res_inveter_company_id[]" id="res_inveter_company_id" multiple>
                            <option disabled>{{ __('message.-- Select --') }}</option>
                            @foreach($inveter_company as $value)
                            @php
                            $selected = (isset($sales_quatation) && in_array($value->id, explode(',', $sales_quatation->inveter_company_id))) ? 'selected' : '';
                            @endphp
                            <option value="{{$value->id}}" {{$selected}}>{{$value->name}}</option>
                            @endforeach
                        </select>
                        <span class="invalid-feedback d-block" id="error_res_inveter_company_id" role="alert"></span>
                    </div>
                    <div class="col-12 col-md-6 col-lg-4 mb-1 custom-input-group">
                        <label class="form-label" for="res_inveter_capacity">{{ __('message.Inveter Capacity') }} <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="res_inveter_capacity" id="res_inveter_capacity" placeholder="{{ __('message.Inveter Capacity') }}" value="{{ ((isset($sales_quatation) && isset($sales_quatation->inveter_capacity)) ? $sales_quatation->inveter_capacity : '')  }}">
                        <span class="invalid-feedback d-block" id="error_res_inveter_capacity" role="alert"></span>
                    </div>
                    <div class="col-12 col-md-6 col-lg-4 mb-1 custom-input-group">
                        <label class="form-label" for="res_no_of_inveter">{{ __('message.No Of Inveter') }} <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="res_no_of_inveter" id="res_no_of_inveter" placeholder="{{ __('message.No Of Inveter') }}" value="{{ ((isset($sales_quatation) && isset($sales_quatation->no_of_inveter)) ? $sales_quatation->no_of_inveter : '')  }}">
                        <span class="invalid-feedback d-block" id="error_res_no_of_inveter" role="alert"></span>
                    </div>
                    <div class="col-12 col-md-12 col-lg-8 mb-1 custom-input-group">
                        <label class="form-label mb-1">{{ __('message.Structure') }}<span class="text-danger">*</span></label><br>
                        <input name="res_structure" id="res_structure_1" class="form-check-input structure_radio" data-type="res" type="radio" value="6ft * 8ft" {{ (!isset($sales_quatation) || $sales_quatation->structure == '6ft * 8ft') ? 'checked' : ''}}>
                        <label for="res_structure_1" class="form-check-label radGroup1">{{ __('message.6ft * 8ft') }} &nbsp; &nbsp;</label>
                        <input name="res_structure" id="res_structure_2" class="form-check-input structure_radio" data-type="res" type="radio" value="8ft * 10ft" {{ isset($sales_quatation) && ($sales_quatation->structure == '8ft * 10ft') ? 'checked' : ''}}>
                        <label for="res_structure_2" class="form-check-label radGroup1">{{ __('message.8ft * 10ft') }}&nbsp; &nbsp;</label>
                        <input name="res_structure" id="res_structure_3" class="form-check-input structure_radio" data-type="res" type="radio" value="10ft * 12ft" {{ isset($sales_quatation) && ($sales_quatation->structure == '10ft * 12ft') ? 'checked' : ''}}>
                        <label for="res_structure_3" class="form-check-label radGroup1">{{ __('message.10ft * 12ft') }}&nbsp; &nbsp;</label>
                        <input name="res_structure" id="res_structure_4" class="form-check-input structure_radio" data-type="res" type="radio" value="As Per Design" {{ isset($sales_quatation) && ($sales_quatation->structure == 'As Per Design') ? 'checked' : ''}}>
                        <label for="res_structure_4" class="form-check-label radGroup1">{{ __('message.As Per Design') }}</label>
                    </div>
                    <div class="col-12 col-md-6 col-lg-4 mb-1 custom-input-group radioeffect">
                        <label class="form-label mb-1">{{ __('message.Common Meter') }}<span class="text-danger">*</span></label><br>
                        <input name="res_common_meter" id="res_cm_1" class="form-check-input res_common_meter" type="radio" data-class="div1" value="Yes" {{ isset($sales_quatation) && ($sales_quatation->common_meter == 'Yes') ? 'checked' : ''}}>
                        <label for="res_cm_1" class="form-check-label radGroup1">{{ __('message.Yes') }} &nbsp; &nbsp;</label>
                        <input name="res_common_meter" id="res_cm_2" class="form-check-input res_common_meter" type="radio" data-class="div2" value="No" {{ (!isset($sales_quatation) || $sales_quatation->common_meter == 'No') ? 'checked' : ''}}>
                        <label for="res_cm_2" class="form-check-label radGroup1">{{ __('message.No') }}</label>
                    </div>
                    <div class="col-12 col-md-6 col-lg-4 mb-1 custom-input-group">
                        <label class="form-label" for="res_reference">{{ __('message.Reference') }} <span class="text-danger"></span></label>
                        <input type="text" class="form-control" name="res_reference" id="res_reference" placeholder="{{ __('message.Reference') }}" value="{{ ((isset($sales_quatation) && isset($sales_quatation->reference)) ? $sales_quatation->reference : '')  }}" oninput="this.value = this.value.toUpperCase()">
                        <span class="invalid-feedback d-block" id="error_res_reference" role="alert"></span>
                    </div>
                    <div class="col-12 col-md-6 col-lg-4 mb-1 custom-input-group">
                        <label class="form-label" for="res_agent_sales_person_id">Agent Sales Person<span class="text-danger">*</span></label>
                        <select class="form-control form-select select2 custom-select2 agent_sales_person_id" name="res_agent_sales_person_id" id="res_agent_sales_person_id">
                            <option selected disabled value="">-- Select --</option>
                            @foreach($agentSalesPerson as $value)
                            <option value="{{$value->id}}" {{ (isset($sales_quatation) && $sales_quatation->agent_sales_person_id == $value->id ) ? 'selected' : '' }}>{{ $value->name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-6 col-lg-4 mb-1 custom-input-group">
                        <label class="form-label" for="res_total_system_cost">{{ __('message.Total System Cost') }} <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="res_total_system_cost" id="res_total_system_cost" placeholder="{{ __('message.Total System Cost') }}" value="{{ ((isset($sales_quatation) && isset($sales_quatation->total_system_cost)) ? $sales_quatation->total_system_cost : '')  }}">
                        <span class="invalid-feedback d-block" id="error_res_total_system_cost" role="alert"></span>
                    </div>
                    <div class="col-12 col-md-6 col-lg-3 mb-1 custom-input-group">
                        <label class="form-label mb-1">Meter Charge Extra</label><br>
                        <input name="res_meter_charge_extra" id="res_mcr_1" class="form-check-input res_meter_charge_extra" type="radio" value="Yes" {{ isset($sales_quatation) && ($sales_quatation->meter_charges_extra == 'Yes') ? 'checked' : ''}}>
                        <label for="res_mcr_1" class="form-check-label mchargeGroup1">{{ __('message.Yes') }} &nbsp; &nbsp; </label>
                        <input name="res_meter_charge_extra" id="res_mcr_2" class="form-check-input res_meter_charge_extra" type="radio" value="No" {{ (!isset($sales_quatation) || $sales_quatation->meter_charges_extra == 'No') ? 'checked' : ''}}>
                        <label for="res_mcr_2" class="form-check-label mchargeGroup1">{{ __('message.No') }}</label>
                    </div>
                    <div class="col-12 col-md-6 col-lg-4 mb-1 custom-input-group">
                        <label class="form-label" for="res_meter_charges">{{ __('message.Meter Charges') }} <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="res_meter_charges" id="res_meter_charges" placeholder="{{ __('message.Meter Charges') }}" value="{{ ((isset($sales_quatation) && isset($sales_quatation->meter_charges)) ? $sales_quatation->meter_charges : '')  }}" {{ isset($sales_quatation) && ($sales_quatation->meter_charges_extra == 'Yes') ? 'checked' : ''}}>
                        <span class="invalid-feedback d-block" id="error_res_meter_charges" role="alert"></span>
                    </div>
                    <div class="col-12 col-md-6 col-lg-4 mb-1 custom-input-group">
                        <label class="form-label" for="res_registration_fee">{{ __('message.Registration Fee') }} <span class="text-danger"></span></label>
                        <input type="number" class="form-control" name="res_registration_fee" id="res_registration_fee" placeholder="{{ __('message.Registration Fee') }}" value="{{ ((isset($sales_quatation) && isset($sales_quatation->registration_fee)) ? $sales_quatation->registration_fee : '')  }}">
                        <span class="invalid-feedback d-block" id="error_res_registration_fee" role="alert"></span>
                    </div>
                    <div class="col-12 col-md-6 col-lg-4 mb-1 custom-input-group">
                        <label class="form-label" for="resident_total_amount">Total Amount <span class="text-danger"></span></label>
                        <input type="text" class="form-control" name="resident_total_amount" id="resident_total_amount" placeholder="Total Amount" value="{{ ((isset($sales_quatation) && isset($sales_quatation->total_amount)) ? $sales_quatation->total_amount : '')  }}" readonly>
                        <span class="invalid-feedback d-block" id="error_resident_total_amount" role="alert"></span>
                    </div>
                    <div class="col-12 col-md-6 col-lg-4 mb-1 custom-input-group">
                        <label class="form-label" for="res_other_charge_name">Other Charge Name <span class="text-danger"></span></label>
                        <input type="text" class="form-control" name="res_other_charge_name" id="res_other_charge_name" placeholder="Other Charge Name" value="{{ ((isset($sales_quatation) && isset($sales_quatation->other_charge_name)) ? $sales_quatation->other_charge_name : '')  }}">
                        <span class="invalid-feedback d-block" id="error_res_other_charge_name" role="alert"></span>
                    </div>
                    <div class="col-12 col-md-6 col-lg-4 mb-1 custom-input-group">
                        <label class="form-label" for="res_other_charge_amount">Other Charge Amount <span class="text-danger"></span></label>
                        <input type="number" class="form-control" name="res_other_charge_amount" id="res_other_charge_amount" placeholder="Other Charge Amount" value="{{ ((isset($sales_quatation) && isset($sales_quatation->other_charge_amount)) ? $sales_quatation->other_charge_amount : '')  }}">
                        <span class="invalid-feedback d-block" id="error_res_other_charge_amount" role="alert"></span>
                    </div>
                    <div class="col-12 col-md-6 col-lg-4 mb-1 custom-input-group">
                        <label class="form-label" for="res_bank_id">Bank<span class="text-danger">*</span></label>
                        <select class="form-control form-select select2 custom-select2 res_bank_id" name="res_bank_id" id="res_bank_id">
                            <option selected disabled value="">-- Select --</option>
                            @foreach($bank as $value)
                            <option value="{{$value->id}}" {{ (isset($sales_quatation) && $sales_quatation->bank_id == $value->id ) ? 'selected' : ($value->default == 1 ? 'selected' : '') }}>{{ $value->name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="accordion mt-3" id="accordionPanelsStayOpen">
                    <div class="accordion-item">
                        <h1 class="accordion-header d-flex" id="panelsStayOpen-headingOne">
                            <button class="accordion-button  bg-warning" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapseOne" aria-expanded="true" aria-controls="panelsStayOpen-collapseOne">
                                <b>Commercial Offer</b>
                            </button>
                        </h1>
                        <div id="panelsStayOpen-collapseOne" class="accordion-collapse collapse show" aria-labelledby="panelsStayOpen-headingOne" data-bs-parent="#accordionPanelsStayOpen">
                            <div class="accordion-body">
                                <!-- Dispaly calculation table -->
                                <table class="table table-bordered">
                                    <tr>
                                        <th style="width: 20px;">Sr. no.</th>
                                        <th style="min-width: 250px;">Description</th>
                                        <th style="width: 150px;">Installed Capacity (KW)</th>
                                        <th style="width: 150px;">Rate / KW</th>
                                        <th style="width: 150px;">Value</th>
                                    </tr>
                                    <tr>
                                        <td>1</td>
                                        <td>Complete Epc Price For Design, Engineering, Supply And Installation And Testing Of Solar Power Generating System</td>
                                        <td id="span_pv_capacity_kw">{{ ((isset($sales_quatation) && isset($sales_quatation->pv_capacity_kw)) ? $sales_quatation->pv_capacity_kw : 0)  }}</td>
                                        <td id="span_rate">{{ ((isset($sales_quatation) && (isset($sales_quatation->total_system_cost)) && $sales_quatation->total_system_cost > 0 && $sales_quatation->pv_capacity_kw > 0) ? number_format(floatval($sales_quatation->total_system_cost) / floatval($sales_quatation->pv_capacity_kw),2, '.', '') : 0)  }}</td>
                                        <td id="span_value">{{ ((isset($sales_quatation) && isset($sales_quatation->total_system_cost)) ? number_format($sales_quatation->total_system_cost,2, '.', '') : 0)  }}</td>
                                    </tr>

                                    <tr>
                                        <td colspan="4" align="right">Meter Charge (Approx)</td>
                                        <td id="span_meter_charges">{{ ((isset($sales_quatation) && isset($sales_quatation->meter_charges)) ? number_format($sales_quatation->meter_charges,2, '.', '') : 0)  }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" align="right">Registration Charges</td>
                                        <td id="span_registration_fee">{{ ((isset($sales_quatation) && isset($sales_quatation->registration_fee)) ? number_format($sales_quatation->registration_fee,2, '.', '') : 0)  }}</td>
                                    </tr>
                                    <tr>
                                        <td id="span_other_charge_name" colspan="4" align="right">Other Charges</td>
                                        <td id="span_other_charge">{{ ((isset($sales_quatation) && isset($sales_quatation->other_charge_amount)) ? number_format($sales_quatation->other_charge_amount,2, '.', '') : 0)  }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" align="right"><b>Total Project Cost (Payable)</b></td>
                                        <td id="span_total_project_cost">{{ ((isset($sales_quatation) && isset($sales_quatation->total_system_cost)) ? number_format(floatval($sales_quatation->total_system_cost) + floatval($sales_quatation->meter_charges) + floatval($sales_quatation->registration_fee) + floatval($sales_quatation->other_charge_amount),2, '.', '') : 0)  }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" align="right"><b>Total Subsidy</b></td>
                                        <td><input type="text" id="span_subsidy" class="form-control p-50" name="span_subsidy" value="{{ ((isset($sales_quatation) && isset($sales_quatation->subsidy)) ? number_format($sales_quatation->subsidy,2, '.', '') : 0)  }}"></td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" align="right"><b>Net Customer Price</b></td>
                                        <td id="span_net_customer_price">{{ ((isset($sales_quatation) && isset($sales_quatation->total_system_cost)) ? number_format((floatval($sales_quatation->total_system_cost) + floatval($sales_quatation->meter_charges) + floatval($sales_quatation->registration_fee) + floatval($sales_quatation->other_charge_amount)) - floatval($sales_quatation->subsidy),2, '.', '') : 0)  }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h1 class="accordion-header  d-flex " id="panelsStayOpen-headingTwo">
                            <button class="accordion-button collapsed bg-warning" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapseTwo" aria-expanded="false" aria-controls="panelsStayOpen-collapseTwo">
                                <b>Technical Specifications & BOM</b>
                            </button>
                        </h1>
                        <div id="panelsStayOpen-collapseTwo" class="accordion-collapse collapse" aria-labelledby="panelsStayOpen-headingTwo" data-bs-parent="#accordionPanelsStayOpen">
                            <div class="accordion-body">
                               @include('admin.sales-quatation.technical_specifications', ['type' => 'res','prefillData' => $technicalSpecification])
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Dispaly calculation table -->
                <div class="col-md-12 mt-2">
                    <button type="submit" class="btn btn-sm btn-primary float-end save_res">{{ __('message.Submit') }}</button>
                    <a role="botton" class="btn btn-sm btn-primary float-end mx-1" href="{{route('sales-quatation.index')}}">{{ __('message.Cancel') }}</a>
                </div>
            </form>
            <form id="roofdataform" action="javascript:void(0)" method="POST" class="d-none">
                @csrf
                <div class="row">
                    <input type="hidden" id="form_type" name="form_type" value="roof">
                    @if((isset($sales_quatation) && isset($sales_quatation->id)))
                    <input type="hidden" id="sales_quatation_id" name="sales_quatation_id" value="{{ $sales_quatation->id }}">
                    <script src="{{asset('app-assets/vendors/js/jquery/jquery.min.js')}}"></script>
                    <script>
                        $(document).ready(function() {
                            var Value = $('.form_type:checked').val();
                            if (Value == 'trading') {
                                $("#res_form").addClass('d-none');
                                $("#roofdataform").addClass('d-none');
                                $("#trading_form").removeClass('d-none');
                            } else if (Value == 'resident') {
                                $("#trading_form").addClass('d-none');
                                $("#res_form").removeClass('d-none');
                                $("#roofdataform").addClass('d-none');
                            } else if (Value == 'roof') {
                                $("#res_form").addClass('d-none');
                                $("#trading_form").addClass('d-none');
                                $("#roofdataform").removeClass('d-none');
                            }
                        });
                    </script>
                    @endif
                    <div class="col-12 col-md-6 col-lg-4 mb-1 custom-input-group">
                        <label class="form-label" for="roof_lead_master_id">{{ __('message.Lead') }} <span class="text-danger">*</span></label>
                        <select class="form-control anlayst form-select select2 custom-select2" name="roof_lead_master_id" id="roof_lead_master_id">
                            <option selected disabled value="">{{ __('message.-- Select --') }}</option>
                            @foreach($lead_complete as $value)
                            <option value="{{$value->id}}" data-mobile="{{$value->mobile}}" data-reference="{{$value->reference}}" data-agent_sales_person_id="{{$value->agent_sales_person_id}}" data-name="{{$value->name}}" {{ (isset($sales_quatation) && ($sales_quatation->lead_master_id == $value->id) ? 'selected' : '')}}>{{$value->name}} - {{$value->mobile}}</option>
                            @endforeach
                        </select>
                        <span class="invalid-feedback d-block" id="error_roof_lead_master_id" role="alert"></span>
                    </div>
                    <div class="col-12 col-md-6 col-lg-4 mb-1 custom-input-group">
                        <label class="form-label" for="quatation_type">{{ __('message.Quatation Type') }} <span class="text-danger">*</span></label>
                        <select class="form-control anlayst form-select select2 custom-select2" name="quatation_type" id="quatation_type">
                            <option selected disabled value="">{{ __('message.-- Select --') }}</option>
                            <option value="industrial" {{ (isset($sales_quatation) && $sales_quatation->quatation_type == 'industrial') ? 'selected' : '' }}>Industrial</option>
                            <option value="commercial" {{ (isset($sales_quatation) && $sales_quatation->quatation_type == 'commercial') ? 'selected' : '' }}>Commercial</option>
                            <option value="resident" {{ (isset($sales_quatation) && $sales_quatation->quatation_type == 'resident') ? 'selected' : '' }}>Resident</option>
                        </select>
                        <span class="invalid-feedback d-block" id="error_quatation_type" role="alert"></span>
                    </div>
                    <div class="col-12 col-md-6 col-lg-4 mb-1 custom-input-group">
                        <label class="form-label" for="roof_mobile">{{ __('message.Mobile') }} <span class="text-danger">*</span></label>
                        <input type="number" maxlength="10" class="form-control" name="roof_mobile" id="roof_mobile" placeholder="{{ __('message.Mobile No.') }}" value="{{ ((isset($sales_quatation) && isset($sales_quatation->mobile)) ? $sales_quatation->mobile : '')  }}">
                        <span class="invalid-feedback d-block" id="error_roof_mobile" role="alert"></span>
                    </div>
                    <div class="col-12 col-md-6 col-lg-4 mb-1 custom-input-group">
                        <label class="form-label" for="roof_name">{{ __('message.Name') }} <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="roof_name" id="roof_name" placeholder="{{ __('message.Name') }}" value="{{ ((isset($sales_quatation) && isset($sales_quatation->name)) ? $sales_quatation->name : '')  }}" oninput="this.value = this.value.toUpperCase()">
                        <span class="invalid-feedback d-block" id="error_roof_name" role="alert"></span>
                    </div>
                    <div class="col-12 col-md-6 col-lg-4 mb-1 custom-input-group">
                        <label class="form-label" for="roof_address">{{ __('message.Bill to Address') }} <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="roof_address" id="roof_address" placeholder="{{ __('message.Bill to Address') }}" value="{{ ((isset($sales_quatation) && isset($sales_quatation->address)) ? $sales_quatation->address : '')  }}">
                        <span class="invalid-feedback d-block" id="error_roof_address" role="alert"></span>
                    </div>
                    <div class="col-12 col-md-6 col-lg-4 mb-1 custom-input-group">
                        <label class="form-label" for="penal_company_id">{{ __('message.Panel Company Name') }} <span class="text-danger">*</span></label>
                        <select class="form-control anlayst" name="penal_company_id[]" id="penal_company_id" multiple>
                            <option disabled>{{ __('message.-- Select --') }}</option>
                            @foreach($penal_company as $value)
                            @php
                            $selected = (isset($sales_quatation) && in_array($value->id, explode(',', $sales_quatation->penal_company_id))) ? 'selected' : '';
                            @endphp
                            <option value="{{$value->id}}" {{$selected}}>{{$value->name}}</option>
                            @endforeach
                        </select>
                        <span class="invalid-feedback d-block" id="error_penal_company_id" role="alert"></span>
                    </div>
                    <div class="col-12 col-md-6 col-lg-4 mb-1 custom-input-group">
                        <label class="form-label" for="penal_type_id">{{ __('message.Panel Type') }} <span class="text-danger">*</span></label>
                        <select class="form-control anlayst select2 custom-select2" name="penal_type_id" id="penal_type_id">
                            <option selected disabled value="">{{ __('message.-- Select --') }}</option>
                            @foreach($penal_type as $value)
                            <option value="{{$value->id}}" {{ (isset($sales_quatation) && ($sales_quatation->penal_type_id == $value->id) ? 'selected' : '')}}>{{$value->name}}</option>
                            @endforeach
                        </select>
                        <span class="invalid-feedback d-block" id="error_penal_type_id" role="alert"></span>
                    </div>
                    <div class="col-12 col-md-6 col-lg-4 mb-1 custom-input-group">
                        <label class="form-label" for="penal_watt_id">{{ __('message.Panel Watt') }} <span class="text-danger">*</span></label>
                        <select class="form-control anlayst select2 custom-select2" name="penal_watt_id" id="penal_watt_id">
                            <option selected disabled value="">{{ __('message.-- Select --') }}</option>
                            @foreach($penal_watt as $value)
                            <option value="{{$value->id}}" {{ (isset($sales_quatation) && ($sales_quatation->penal_watt_id == $value->id) ? 'selected' : '')}}>{{$value->name}}</option>
                            @endforeach
                        </select>
                        <span class="invalid-feedback d-block" id="error_penal_watt_id" role="alert"></span>
                    </div>
                    <div class="col-12 col-md-6 col-lg-4 mb-1 custom-input-group">
                        <label class="form-label" for="penal_nos">{{ __('message.Panel Nos') }} <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="penal_nos" id="penal_nos" placeholder="{{ __('message.Panel Nos') }}" value="{{ ((isset($sales_quatation) && isset($sales_quatation->penal_nos)) ? $sales_quatation->penal_nos : '')  }}">
                        <span class="invalid-feedback d-block" id="error_penal_nos" role="alert"></span>
                    </div>
                    <div class="col-12 col-md-6 col-lg-4 mb-1 custom-input-group">
                        <label class="form-label" for="pv_capacity_kw">{{ __('message.PV Capacity Kw') }} <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="pv_capacity_kw" id="pv_capacity_kw" placeholder="{{ __('message.PV Capacity Kw') }}" value="{{ ((isset($sales_quatation) && isset($sales_quatation->pv_capacity_kw)) ? $sales_quatation->pv_capacity_kw : '')  }}" readonly>
                        <span class="invalid-feedback d-block" id="error_pv_capacity_kw" role="alert"></span>
                    </div>
                    <div class="col-12 col-md-6 col-lg-4 mb-1 custom-input-group">
                        <label class="form-label" for="inveter_company_id">{{ __('message.Inveter Company Name') }} <span class="text-danger">*</span></label>
                        <select class="form-control anlayst" name="inveter_company_id[]" id="inveter_company_id" multiple>
                            <option disabled>{{ __('message.-- Select --') }}</option>
                            @foreach($inveter_company as $value)
                            @php
                            $selected = (isset($sales_quatation) && in_array($value->id, explode(',', $sales_quatation->inveter_company_id))) ? 'selected' : '';
                            @endphp
                            <option value="{{$value->id}}" {{$selected}}>{{$value->name}}</option>
                            @endforeach
                        </select>
                        <span class="invalid-feedback d-block" id="error_inveter_company_id" role="alert"></span>
                    </div>
                    <div class="col-12 col-md-6 col-lg-4 mb-1 custom-input-group">
                        <label class="form-label" for="inveter_capacity">{{ __('message.Inveter Capacity') }} <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="inveter_capacity" id="inveter_capacity" placeholder="{{ __('message.Inveter Capacity') }}" value="{{ ((isset($sales_quatation) && isset($sales_quatation->inveter_capacity)) ? $sales_quatation->inveter_capacity : '')  }}">
                        <span class="invalid-feedback d-block" id="error_inveter_capacity" role="alert"></span>
                    </div>
                    <div class="col-12 col-md-6 col-lg-4 mb-1 custom-input-group">
                        <label class="form-label" for="no_of_inveter">{{ __('message.No Of Inveter') }} <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="no_of_inveter" id="no_of_inveter" placeholder="{{ __('message.No Of Inveter') }}" value="{{ ((isset($sales_quatation) && isset($sales_quatation->no_of_inveter)) ? $sales_quatation->no_of_inveter : '')  }}">
                        <span class="invalid-feedback d-block" id="error_no_of_inveter" role="alert"></span>
                    </div>
                    <div class="col-12 col-md-12 col-lg-8 mb-1 custom-input-group">
                        <label class="form-label mb-1">{{ __('message.Structure') }}<span class="text-danger">*</span></label><br>
                        <input name="structure" id="structure_1" class="form-check-input structure_radio" data-type="roof" type="radio" value="6ft * 8ft" {{ (!isset($sales_quatation) || $sales_quatation->structure == '6ft * 8ft') ? 'checked' : ''}}>
                        <label for="structure_1" class="form-check-label radGroup1">{{ __('message.6ft * 8ft') }} &nbsp; &nbsp;</label>
                        <input name="structure" id="structure_2" class="form-check-input structure_radio" data-type="roof" type="radio" value="8ft * 10ft" {{ isset($sales_quatation) && ($sales_quatation->structure == '8ft * 10ft') ? 'checked' : ''}}>
                        <label for="structure_2" class="form-check-label radGroup1">{{ __('message.8ft * 10ft') }}&nbsp; &nbsp;</label>
                        <input name="structure" id="structure_3" class="form-check-input structure_radio" data-type="roof" type="radio" value="10ft * 12ft" {{ isset($sales_quatation) && ($sales_quatation->structure == '10ft * 12ft') ? 'checked' : ''}}>
                        <label for="structure_3" class="form-check-label radGroup1">{{ __('message.10ft * 12ft') }}&nbsp; &nbsp;</label>
                        <input name="structure" id="structure_4" class="form-check-input structure_radio" data-type="roof" type="radio" value="As Per Design" {{ isset($sales_quatation) && ($sales_quatation->structure == 'As Per Design') ? 'checked' : ''}}>
                        <label for="structure_4" class="form-check-label radGroup1">{{ __('message.As Per Design') }}</label>
                    </div>

                    <div class="col-12 col-md-6 col-lg-4 mb-1 custom-input-group">
                        <label class="form-label" for="roof_reference">{{ __('message.Reference') }} <span class="text-danger"></span></label>
                        <input type="text" class="form-control" name="roof_reference" id="roof_reference" placeholder="{{ __('message.Reference') }}" value="{{ ((isset($sales_quatation) && isset($sales_quatation->reference)) ? $sales_quatation->reference : '')  }}" oninput="this.value = this.value.toUpperCase()">
                        <span class="invalid-feedback d-block" id="error_roof_reference" role="alert"></span>
                    </div>
                    <div class="col-12 col-md-6 col-lg-4 mb-1 custom-input-group">
                        <label class="form-label" for="roof_agent_sales_person_id">Agent Sales Person<span class="text-danger">*</span></label>
                        <select class="form-control form-select select2 custom-select2 agent_sales_person_id" name="roof_agent_sales_person_id" id="roof_agent_sales_person_id">
                            <option selected disabled value="">-- Select --</option>
                            @foreach($agentSalesPerson as $value)
                            <option value="{{$value->id}}" {{ (isset($sales_quatation) && $sales_quatation->agent_sales_person_id == $value->id ) ? 'selected' : '' }}>{{ $value->name}}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-md-6 col-lg-4 mb-1 custom-input-group radioeffect">
                        <label class="form-label mb-1">{{ __('message.GST') }}<span class="text-danger">*</span></label><br>
                        <input name="gst" id="gst_1" class="form-check-input roof_gst" type="radio" data-class="div1" value="Including" {{ (isset($sales_quatation) && $sales_quatation->gst == 'Including') ? 'checked' : ''}}>
                        <label for="gst_1" class="form-check-label radGroup1">{{ __('message.Including') }} &nbsp; &nbsp;</label>
                        <input name="gst" id="gst_2" class="form-check-input roof_gst" type="radio" data-class="div2" value="Extra" {{ (!isset($sales_quatation) || $sales_quatation->gst == 'Extra') ? 'checked' : '' }}>
                        <label for="gst_2" class="form-check-label radGroup1">{{ __('message.Extra') }}</label>
                    </div>
                    <div class="col-12 col-md-6 col-lg-4 mb-1 custom-input-group">
                        <label class="form-label" for="rate_per_kw">{{ __('message.Rate Per KW') }} <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="rate_per_kw" id="rate_per_kw" placeholder="{{ __('message.Rate Per KW') }}" value="{{ ((isset($sales_quatation) && isset($sales_quatation->rate_per_kw)) ? $sales_quatation->rate_per_kw : '')  }}">
                        <span class="invalid-feedback d-block" id="error_rate_per_kw" role="alert"></span>
                    </div>
                    <div class="col-12 col-md-6 col-lg-3 mb-1 custom-input-group">
                        <label class="form-label mb-1">Meter Charge Extra</label><br>
                        <input name="meter_charge_extra" id="mcr_1" class="form-check-input meter_charge_extra" type="radio" value="Yes" {{ isset($sales_quatation) && ($sales_quatation->meter_charges_extra == 'Yes') ? 'checked' : ''}}>
                        <label for="mcr_1" class="form-check-label amchargeGroup1">{{ __('message.Yes') }} &nbsp; &nbsp;</label>
                        <input name="meter_charge_extra" id="mcr_2" class="form-check-input meter_charge_extra" type="radio" value="No" {{ (!isset($sales_quatation) || $sales_quatation->meter_charges_extra == 'No') ? 'checked' : ''}}>
                        <label for="mcr_2" class="form-check-label amchargeGroup1">{{ __('message.No') }}</label>
                    </div>
                    <div class="col-12 col-md-6 col-lg-4 mb-1 custom-input-group">
                        <label class="form-label" for="meter_charges">{{ __('message.Meter Charges') }} <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="meter_charges" id="meter_charges" placeholder="{{ __('message.Meter Charges') }}" value="{{ ((isset($sales_quatation) && isset($sales_quatation->meter_charges)) ? $sales_quatation->meter_charges : '')  }}" {{ isset($sales_quatation) && ($sales_quatation->meter_charges_extra == 'Yes') ? 'readonly' : ''}}>
                        <span class="invalid-feedback d-block" id="error_meter_charges" role="alert"></span>
                    </div>
                    <div class="col-12 col-md-6 col-lg-4 mb-1 custom-input-group">
                        <label class="form-label" for="registration_fee">{{ __('message.Registration Fee') }} <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="registration_fee" id="registration_fee" placeholder="{{ __('message.Registration Fee') }}" value="{{ ((isset($sales_quatation) && isset($sales_quatation->registration_fee)) ? $sales_quatation->registration_fee : '')  }}">
                        <span class="invalid-feedback d-block" id="error_registration_fee" role="alert"></span>
                    </div>
                    <div class="col-12 col-md-6 col-lg-4 mb-1 custom-input-group">
                        <label class="form-label" for="roof_other_charge_name">Other Charge Name <span class="text-danger"></span></label>
                        <input type="text" class="form-control" name="roof_other_charge_name" id="roof_other_charge_name" placeholder="Other Charge Name" value="{{ ((isset($sales_quatation) && isset($sales_quatation->other_charge_name)) ? $sales_quatation->other_charge_name : '')  }}">
                        <span class="invalid-feedback d-block" id="error_roof_other_charge_name" role="alert"></span>
                    </div>
                    <div class="col-12 col-md-6 col-lg-4 mb-1 custom-input-group">
                        <label class="form-label" for="roof_other_charge_amount">Other Charge Amount <span class="text-danger"></span></label>
                        <input type="number" class="form-control" name="roof_other_charge_amount" id="roof_other_charge_amount" placeholder="Other Charge Amount" value="{{ ((isset($sales_quatation) && isset($sales_quatation->other_charge_amount)) ? $sales_quatation->other_charge_amount : '')  }}">
                        <span class="invalid-feedback d-block" id="error_roof_other_charge_amount" role="alert"></span>
                    </div>
                    <div class="col-12 col-md-6 col-lg-4 mb-1 custom-input-group">
                        <label class="form-label" for="roof_bank_id">Bank<span class="text-danger">*</span></label>
                        <select class="form-control form-select select2 custom-select2 roof_bank_id" name="roof_bank_id" id="roof_bank_id">
                            <option selected disabled value="">-- Select --</option>
                            @foreach($bank as $value)
                            <option value="{{$value->id}}" {{ (isset($sales_quatation) && $sales_quatation->bank_id == $value->id ) ? 'selected' : ($value->default == 1 ? 'selected' : '') }}>{{ $value->name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="accordion mt-3" id="accordionPanelsStayOpenRoof">
                    <div class="accordion-item">
                        <h1 class="accordion-header d-flex" id="panelsStayOpen-headingOneRoof">
                            <button class="accordion-button  bg-warning" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapseOneRoof" aria-expanded="true" aria-controls="panelsStayOpen-collapseOneRoof">
                                <b>Commercial Offer</b>
                            </button>
                        </h1>
                        <div id="panelsStayOpen-collapseOneRoof" class="accordion-collapse collapse show" aria-labelledby="panelsStayOpen-headingOneRoof" data-bs-parent="#accordionPanelsStayOpenRoof">
                            <div class="accordion-body">
                                <!-- Dispaly calculation table -->
                                <table class="table table-hover table-bordered">
                                    <tr>
                                        <th>Sr. no.</th>
                                        <th>Description</th>
                                        <th>Installed Capacity (KW)</th>
                                        <th>Rate / KW</th>
                                        <th>Value</th>
                                    </tr>
                                    <tr>
                                        <td>1</td>
                                        <td>Complete Epc Price For Design, Engineering, Supply And Installation And Testing Of Solar Power Generating System</td>
                                        <td id="roof_span_pv_capacity_kw">0</td>
                                        <td id="roof_span_rate">0</td>
                                        <td id="roof_span_value">0</td>
                                    </tr>
                                    <tr>
                                        <td>2</td>
                                        <td><b>Payable GST Amount :- </b><br>On 70% of Contract Value as Goods, Taxable @ 5% On Balance 30% as services, Taxable @ 18%</td>
                                        <td></td>
                                        <td id="roof_span_val1">0</td>
                                        <td id="roof_span_val2">0</td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" align="right"><b>Total Project With GST Cost</b></td>
                                        <td id="roof_span_total_with_gst">0</td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" align="right">DISCOM Meter Connectivity Charge (Approx)</td>
                                        <td id="roof_span_meter_charge">0</td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" align="right">Registration Charges</td>
                                        <td id="roof_span_registration_charge">0</td>
                                    </tr>
                                    <tr>
                                        <td id="roof_span_other_charge_name" colspan="4" align="right">Other Charges</td>
                                        <td id="roof_span_other_charge_amount">0</td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" align="right">
                                            <b>Total Project Cost (Payable)</b>
                                            <input type="hidden" value="0" name="roof_span_total_project_cost_hidden" id="roof_span_total_project_cost_hidden" />
                                        </td>
                                        <td id="roof_span_total_project_cost">0</td>
                                    </tr>
                                </table>
                                <!-- Dispaly calculation table -->
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h1 class="accordion-header  d-flex " id="panelsStayOpen-headingTwoRoof">
                            <button class="accordion-button collapsed bg-warning" type="button" data-bs-toggle="collapse" data-bs-target="#panelsStayOpen-collapseTwoRoof" aria-expanded="false" aria-controls="panelsStayOpen-collapseTwoRoof">
                                <b>Technical Specifications & BOM</b>
                            </button>
                        </h1>
                        <div id="panelsStayOpen-collapseTwoRoof" class="accordion-collapse collapse" aria-labelledby="panelsStayOpen-headingTwoRoof" data-bs-parent="#accordionPanelsStayOpenRoof">
                            <div class="accordion-body">
                                @include('admin.sales-quatation.technical_specifications', ['type' => 'roof','prefillData' => $technicalSpecification])
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-12 mt-2">
                    <button type="submit" class="btn btn-sm btn-primary float-end save_roof">{{ __('message.Submit') }}</button>
                    <a role="botton" class="btn btn-sm btn-primary float-end mx-1" href="{{route('sales-quatation.index')}}">{{ __('message.Cancel') }}</a>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
@section('pagescript')
<script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote.min.js"></script>
<script>
    const sr_no = 3;

    $(document).on("change", ".structure_radio", function() {
        let type = $(this).data('type');
        let me = $(this).val();
        $("."+type+"_structure_size").val(me);


    });
    $(document).on("click", ".variant-delete", function() {
        $(this).closest('tr').remove();
        var type = $(this).data('type');
        updateSrNo(type);
    })

    $(document).on("click", ".add-more-res", function() {
        var lastRow = $('.tech_res_table tbody tr:last').clone();

        lastRow.find('input, textarea').val('');
        var lastSerial = parseInt($('.tech_res_table tbody tr:last .sr_no').text());
        lastRow.find('.sr_no').text(lastSerial + 1);
        $('.tech_res_table tbody').append(lastRow);
    });

    $(document).on("click", ".add-more-roof", function() {
        var lastRow = $('.tech_roof_table tbody tr:last').clone();
        lastRow.find('input, textarea').val('');
        var lastSerial = parseInt($('.tech_roof_table tbody tr:last .sr_no').text());
        lastRow.find('.sr_no').text(lastSerial + 1);
        $('.tech_roof_table tbody').append(lastRow);
    });

    function updateSrNo(type) {
        $('.' + type).each(function(index) {
            $(this).find('.sr_no').text(index + 3);
        });
    }

    $('.editor').summernote({
        placeholder: '',
        tabsize: 2,
        height: 100,
        toolbar: [
            ['font', ['bold', 'underline']]
        ]
    });
    $(document).ready(function() {

        let res_meter_charges_radio = 'No';

        $(document).on('change', '.res_meter_charge_extra', function() {
            if ($(this).val() == 'Yes') {
                res_meter_charges_radio = $(this).val();
                $("#res_meter_charges").val('0');
                $("#res_meter_charges").trigger('keyup');
                $("#span_meter_charges").html('Extra');
                $("#res_meter_charges").attr('readonly', true);
            } else {
                res_meter_charges_radio = 'No';
                $("#res_meter_charges").val('0');
                $("#res_meter_charges").trigger('keyup');
                $("#res_meter_charges").removeAttr('readonly', true);
            }
        });

        $(document).on('change', '.meter_charge_extra', function() {
            if ($(this).val() == 'Yes') {
                $("#meter_charges").val('0');
                $("#meter_charges").trigger('keyup');
                $("#roof_span_meter_charge").html('Extra');
                $("#meter_charges").attr('readonly', true);
            } else {
                $("#meter_charges").val('0');
                $("#meter_charges").trigger('keyup');
                $("#meter_charges").removeAttr('readonly', true);
            }
        });

        $(document).on('keypress', '#res_penal_nos', function() {
            if ($("#res_penal_nos").val().length > 11) {
                $("#res_penal_nos").attr('type', 'text');
            } else {
                $("#res_penal_nos").attr('type', 'number');
            }
        });
        $(document).on('keypress', '#res_pv_capacity_kw', function() {
            if ($("#res_pv_capacity_kw").val().length > 20) {
                $("#res_pv_capacity_kw").attr('type', 'text');
            } else {
                $("#res_pv_capacity_kw").attr('type', 'number');
            }
        });
        $(document).on('keypress', '#res_inveter_capacity', function() {
            if ($("#res_inveter_capacity").val().length > 11) {
                $("#res_inveter_capacity").attr('type', 'text');
            } else {
                $("#res_inveter_capacity").attr('type', 'number');
            }
        });
        $(document).on('keypress', '#res_no_of_inveter', function() {
            if ($("#res_no_of_inveter").val().length > 11) {
                $("#res_no_of_inveter").attr('type', 'text');
            } else {
                $("#res_no_of_inveter").attr('type', 'number');
            }
        });
        $(document).on('keypress', '#res_meter_charges', function() {
            if ($("#res_meter_charges").val().length > 11) {
                $("#res_meter_charges").attr('type', 'text');
            } else {
                $("#res_meter_charges").attr('type', 'number');
            }
        });
        $(document).on('keypress', '#res_registration_fee', function() {
            if ($("#res_registration_fee").val().length > 11) {
                $("#res_registration_fee").attr('type', 'text');
            } else {
                $("#res_registration_fee").attr('type', 'number');
            }
        });
        $(document).on('keypress', '#mobile', function() {
            if ($("#mobile").val().length > 9) {
                $("#mobile").attr('type', 'text');
            } else {
                $("#mobile").attr('type', 'number');
            }
        });
        $(document).on('keypress', '#roof_mobile', function() {
            if ($("#roof_mobile").val().length > 9) {
                $("#roof_mobile").attr('type', 'text');
            } else {
                $("#roof_mobile").attr('type', 'number');
            }
        });
        $(document).on('keypress', '#res_mobile', function() {
            if ($("#res_mobile").val().length > 9) {
                $("#res_mobile").attr('type', 'text');
            } else {
                $("#res_mobile").attr('type', 'number');
            }
        });

        $('#res_penal_company_id,#res_inveter_company_id,#penal_company_id,#inveter_company_id,.item_id').select2({
            placeholder: "-- Select --",
            allowClear: true
        });
        $(document).on('change', '.item_id', function() {
            var selectedOption = $(this).find('option:selected');
            var $repeaterItem = $(this).closest('[data-repeater-item]');
            var gstRate = $(this).find('option:selected').data('gst_rate');
            $repeaterItem.find('#item_gst').val(gstRate);
        });

        $(document).on('change', '.item_group_id', function() {
            var selectedOption = $(this).find('option:selected');
            var $repeaterItem = $(this).closest('[data-repeater-item]');
            var gstRate = $(this).find('option:selected').data('gst_rate');
            $repeaterItem.find('#item_gst').val(gstRate);
        });
        // Total for trading
        function calculateTotal($repeaterItem) {
            var total = 0;
            $('.nos').each(function() {
                var gst_val = $('.trading_gst:checked').val();
                if (gst_val == 'Extra') {
                    var nos = parseFloat($(this).closest('[data-repeater-item]').find('.nos').val()) || 0;
                    var rate = parseFloat($(this).closest('[data-repeater-item]').find('.rate').val()) || 0;
                    var item_gst = parseFloat($(this).closest('[data-repeater-item]').find('.item_gst').val()) || 0;
                    var sub = nos * rate;
                    var subTota = sub * item_gst / 100;
                    var subTotal = sub + subTota;
                    total += subTotal;
                } else {
                    var nos = parseFloat($(this).closest('[data-repeater-item]').find('.nos').val()) || 0;
                    var rate = parseFloat($(this).closest('[data-repeater-item]').find('.rate').val()) || 0;
                    var subTotal = nos * rate;
                    total += subTotal;
                }
            });
            $('#trading_total_amount').val(total.toFixed(2));
        }
        $(document).on('keyup', '.nos, .rate', function() {
            var $repeaterItem = $(this).closest('[data-repeater-item]');

            var nos = $(this).closest('[data-repeater-item]').find('.nos').val();
            var rate = $(this).closest('[data-repeater-item]').find('.rate').val();
            var sub = nos * rate;
            $(this).closest('[data-repeater-item]').find('.total_taxable').val(sub)

            calculateTotal($repeaterItem);
        });
        $('[data-repeater-item]').each(function() {
            calculateTotal($(this));
        });
        $('.trading_gst').on('change', function() {
            $('[data-repeater-item]').each(function() {
                calculateTotal($(this));
            });
        });
        // Total for trading
        // delete meta record
        $(document).on('click', '.remove-item', function() {
            if (($('.remove-item').length) > 1) {
                $(this).parent().parent().parent().remove();
                $('[data-repeater-item]').each(function() {
                    calculateTotal($(this));
                });
            } else {
                Swal.fire({
                    text: "Cannot remove first item",
                    icon: 'warning',
                    confirmButtonText: 'OK',
                });
            }
        });
        // delete meta record
        // Total for resident with subsidy
        $(document).on('keyup', '#res_total_system_cost, #res_meter_charges, #res_registration_fee,#res_other_charge_amount', function() {
            var res_total_system_cost = ($('#res_total_system_cost').val() == '') ? 0 : $('#res_total_system_cost').val();
            var res_meter_charges = ($('#res_meter_charges').val() == '') ? 0 : $('#res_meter_charges').val();
            var res_registration_fee = ($('#res_registration_fee').val() == '') ? 0 : $('#res_registration_fee').val();
            var res_other_charge_amount = ($('#res_other_charge_amount').val() == '') ? 0 : $('#res_other_charge_amount').val();
            total = (parseFloat(res_total_system_cost) + parseFloat(res_meter_charges) + parseFloat(res_registration_fee) + parseFloat(res_other_charge_amount)).toFixed(2);
            $('#resident_total_amount').val(total);
            // table total param

            if (res_meter_charges_radio == 'No') {
                $('#span_meter_charges').text(res_meter_charges);
            } else {
                $('#span_meter_charges').text('Extra');
            }

            $('#span_registration_fee').text(res_registration_fee);
            $('#span_other_charge').text(res_other_charge_amount);
            $('#span_total_project_cost').text(total);

            var subsidy = $('#span_subsidy').val();
            net_customer_price = parseFloat(total) - parseFloat(subsidy);
            $('#span_net_customer_price').text(net_customer_price);
            // table total param
        });
        $(document).on('keyup', '#res_other_charge_name', function() {
            var res_other_charge_name = ($('#res_other_charge_name').val() == '') ? 'Other Charges' : $('#res_other_charge_name').val();
            $('#span_other_charge_name').text(res_other_charge_name);
        });
        $(document).on('keyup', '#roof_other_charge_name', function() {
            var roof_other_charge_name = ($('#roof_other_charge_name').val() == '') ? 'Other Charges' : $('#roof_other_charge_name').val();
            $('#roof_span_other_charge_name').text(roof_other_charge_name);
        });
        // Total for resident with subsidy
    });
    // Solar RoofTop calculation
    $(document).on('keyup', '#penal_nos', function() {
        calculation();
    });
    $(document).on('change', '#penal_watt_id', function() {
        calculation();
    });

    function calculation() {
        var penal_nos = ($('#penal_nos').val() == '') ? 0 : $('#penal_nos').val();
        var penal_watt_id = ($('#penal_watt_id option:selected').text() == '') ? 0 : $('#penal_watt_id option:selected').text();
        pv_capacity_kw = ((parseFloat(penal_watt_id) * parseFloat(penal_nos)) / 1000).toFixed(3);
        $('#pv_capacity_kw').val(pv_capacity_kw);
        $('#roof_span_pv_capacity_kw').text(pv_capacity_kw);
    }
    $(document).on('keyup', '#rate_per_kw,#registration_fee,#meter_charges,#roof_other_charge_amount,#penal_nos', function() {
        roofTableCalculation();
    });
    $(document).on('change', '.roof_gst,#penal_watt_id', function() {
        roofTableCalculation();
    });

    function roofTableCalculation() {
        var pv_capacity_kw = ($('#pv_capacity_kw').val() == '') ? 0 : $('#pv_capacity_kw').val();
        var rate_per_kw = ($('#rate_per_kw').val() == '') ? 0 : $('#rate_per_kw').val();
        var registration_fee = ($('#registration_fee').val() == '') ? 0 : $('#registration_fee').val();
        var meter_charges = ($('#meter_charges').val() == '') ? 0 : $('#meter_charges').val();
        var roof_other_charge_amount = ($('#roof_other_charge_amount').val() == '') ? 0 : $('#roof_other_charge_amount').val();
        value = (parseFloat(pv_capacity_kw) * parseFloat(rate_per_kw)).toFixed(2);
        $('#roof_span_rate').text(rate_per_kw);

        let meter_charge_extra_radio = $('input[name="meter_charge_extra"]:checked').val();

        if (meter_charge_extra_radio == 'No') {
            $('#roof_span_meter_charge').text(meter_charges);
        } else {
            $('#roof_span_meter_charge').text('Extra');
        }

        $('#roof_span_registration_charge').text(registration_fee);
        $('#roof_span_other_charge_amount').text(roof_other_charge_amount);
        var per = '{{ env("PER") }}';

        var roof_gst_val = $('.roof_gst:checked').val();
        var val4 = parseFloat(rate_per_kw);

        if (roof_gst_val == 'Extra') {
            var value3 = (rate_per_kw * parseFloat(per)) / 100;
            value1 = parseFloat(value);
            value2 = (value1 * parseFloat(per)) / 100;
        } else {
            let a = parseFloat(value) * 100;
            let b = parseFloat(per) + 100;
            value1 = (a / b);
            value2 = (value1 * per) / 100;

            let c = ((parseFloat(rate_per_kw) * 100) * per) / 100;
            var value3 = (c / b);
            val4 = parseFloat(rate_per_kw) - value3;
        }

        $('#roof_span_value').text((parseFloat(pv_capacity_kw) * parseFloat(val4)).toFixed(2));

        // total_with_gst = (parseFloat(value) + parseFloat(value2)).toFixed(2);
        total_with_gst = ((parseFloat(pv_capacity_kw) * parseFloat(val4)) + parseFloat(value2)).toFixed(2);
        total_project_cost = (parseFloat(total_with_gst) + parseFloat(registration_fee) + parseFloat(meter_charges) + parseFloat(roof_other_charge_amount)).toFixed(2);

        $('#roof_span_rate').text(val4.toFixed(2));
        $('#roof_span_val1').text(value3.toFixed(2));
        $('#roof_span_val2').text(value2.toFixed(2));
        $('#roof_span_total_with_gst').text(total_with_gst);
        $('#roof_span_total_project_cost').text(total_project_cost);
        $('#roof_span_total_project_cost_hidden').val(total_project_cost);
    }
    // Solar RoofTop calculation

    $(document).on('keyup', '#res_penal_nos', function() {
        calculationres();
    });
    $(document).on('change', '#res_penal_watt_id,.res_common_meter', function() {
        calculationres();
    });

    function calculationres() {
        var penal_nos = ($('#res_penal_nos').val() == '') ? 0 : $('#res_penal_nos').val();
        var penal_watt_id = ($('#res_penal_watt_id option:selected').text() == '') ? 0 : $('#res_penal_watt_id option:selected').text();
        pv_capacity_kw = ((parseFloat(penal_watt_id) * parseFloat(penal_nos)) / 1000).toFixed(3);
        $('#res_pv_capacity_kw').val(pv_capacity_kw);
        // table value
        $('#span_pv_capacity_kw').text(pv_capacity_kw);
        // table value
        tableCalculation();
        subsidyCalculation();
    }
    // table display calculation
    $(document).on('keyup', '#res_total_system_cost', function() {
        tableCalculation();
        subsidyCalculation();
    });

    function tableCalculation() {
        var res_total_system_cost = ($('#res_total_system_cost').val() == '') ? 0 : $('#res_total_system_cost').val();
        var res_meter_charges = ($('#res_meter_charges').val() == '') ? 0 : $('#res_meter_charges').val();
        var res_registration_fee = ($('#res_registration_fee').val() == '') ? 0 : $('#res_registration_fee').val();

        var res_pv_capacity_kw = ($('#res_pv_capacity_kw').val() == '') ? 0 : $('#res_pv_capacity_kw').val();
        if (res_pv_capacity_kw != 0) {
            rate_kw = (parseFloat(res_total_system_cost) / parseFloat(res_pv_capacity_kw)).toFixed(2);
        } else {
            rate_kw = 0;
        }
        total_project_cos = parseFloat(res_total_system_cost) + parseFloat(res_meter_charges) + parseFloat(res_registration_fee);
        $('#span_rate').text(rate_kw);
        $('#span_value').text(res_total_system_cost);
        $('#span_total_project_cost').text(total_project_cos);

        var subsidy = $('#span_subsidy').val();
        net_customer_price = parseFloat(total_project_cos) - parseFloat(subsidy);
        $('#span_net_customer_price').text(net_customer_price);
    }
    // table display calculation
    // Resident Subsidy calculation
    function subsidyCalculation() {
        var res_common_meter_val = $('.res_common_meter:checked').val();
        var res_pv_capacity_kw = ($('#res_pv_capacity_kw').val() == '') ? 0 : $('#res_pv_capacity_kw').val();
        var subsidy = 0;
        if (res_common_meter_val == 'Yes') {
            if (res_pv_capacity_kw > 500) {
                subsidy = (500 * 18000).toFixed(2);
            } else {
                subsidy = (parseFloat(res_pv_capacity_kw) * 18000).toFixed(2);
            }
        } else if (res_common_meter_val == 'No') {
            if (res_pv_capacity_kw > 3) {
                subsidy = 78000;
            } else if (res_pv_capacity_kw <= 2) {
                subsidy = (res_pv_capacity_kw * 30000).toFixed(2);
            } else {
                subsidy = (2 * 30000 + (res_pv_capacity_kw - 2) * 18000).toFixed(2);
            }
        }
        $('#span_subsidy').val(subsidy);
    }
    // Resident Subsidy calculation
    $(document).ready(function() {

           const urlParams = new URLSearchParams(window.location.search);
            let leadId = urlParams.get('id');

            if (leadId) {
                setTimeout(function () {
                        $("#lead_master_id").val(leadId).trigger('change');
                }, 100);
            }

        $('#lead_master_id').change(function() {
            var selectedOption = $(this).find('option:selected');
            var mobile = selectedOption.data('mobile');
            var name = selectedOption.data('name');
            var reference = selectedOption.data('reference');
            var agent_sales_person_id = selectedOption.data('agent_sales_person_id');

            $('#mobile').val(mobile);
            $('#name').val(name);
            $('#reference').val(reference);
            $('#agent_sales_person_id').val(agent_sales_person_id).trigger('change');
        });
        $('#res_lead_master_id').change(function() {
            var selectedOption = $(this).find('option:selected');
            var mobile = selectedOption.data('mobile');
            var name = selectedOption.data('name');
            var reference = selectedOption.data('reference');
            var agent_sales_person_id = selectedOption.data('agent_sales_person_id');
            $('#res_mobile').val(mobile);
            $('#res_name').val(name);
            $('#res_reference').val(reference);
            $('#res_agent_sales_person_id').val(agent_sales_person_id).trigger('change');
        });
        $('#roof_lead_master_id').change(function() {
            var selectedOption = $(this).find('option:selected');
            var mobile = selectedOption.data('mobile');
            var name = selectedOption.data('name');
            var reference = selectedOption.data('reference');
            var agent_sales_person_id = selectedOption.data('agent_sales_person_id');
            $('#roof_mobile').val(mobile);
            $('#roof_name').val(name);
            $('#roof_reference').val(reference);
            $('#roof_agent_sales_person_id').val(agent_sales_person_id).trigger('change');
        });

        $("#mobile").on('change', function() {
            var mobile = $(this).val();
            $.ajax({
                type: "post",
                url: "{{route('sales-quatation-get-details')}}",
                data: {
                    "_token": "{{ csrf_token() }}",
                    "mobile": mobile
                },
                dataType: 'json',
                beforeSend: function() {
                    $("#name").val('');
                    $("#address").val('');
                    $("#ship_to").val('');
                    $("#gst_no").val('');
                },
                success: function(response) {
                    if (response.status) {
                        $("#name").val(response.salesQuatation.name);
                        $("#address").val(response.salesQuatation.address);
                        $("#ship_to").val(response.salesQuatation.ship_to);
                        $("#gst_no").val(response.salesQuatation.gst_no);
                    }
                }
            });

        });

        $("#trading_form").validate({
            rules: {
                lead_master_id: {
                    required: true
                },
                name: {
                    required: true
                },
                mobile: {
                    required: true,
                    minlength: 10,
                    regex: "[6-7-8-9]{1}[0-9]{9}"
                },
                address: {
                    required: true
                },
                ship_to: {
                    required: true
                },
                gst_no: {
                    // required: true,
                    regex: /^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{3}$/
                },
                item_id: {
                    required: true
                },
                nos: {
                    required: true
                },
                rate: {
                    required: true
                },
                agent_sales_person_id: {
                    required: true
                },
            },
            messages: {
                lead_master_id: "Please Select Lead",
                name: "{{ __('message.Enter Name') }}",
                mobile: {
                    required: "{{ __('message.Enter Mobile Number') }}",
                    minlength: "{{ __('message.Enter at least 10 digits') }}",
                    regex: "{{ __('message.Enter Valid Number') }}"
                },
                address: "{{ __('message.Enter Address') }}",
                ship_to: "{{ __('message.Enter Ship To') }}",
                gst_no: {
                    // required: "{{ __('message.Enter GST Number') }}",
                    regex: "{{ __('message.Enter Valid Number') }}"
                },
                item_id: "{{ __('message.Select Item') }}",
                nos: "{{ __('message.Enter Nos') }}",
                rate: "{{ __('message.Enter Rate') }}",
                agent_sales_person_id: "Select Agent Sales Person",
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
        $(document).on('click', '.save_tra', function(event) {
            event.preventDefault();
            if ($("#trading_form").valid()) {
                var formData = new FormData($("#trading_form")[0]);
                $.ajax({
                    type: "POST",
                    url: "{{route('sales-quatation.store')}}",
                    data: formData,
                    dataType: 'json',
                    cache: false,
                    contentType: false,
                    processData: false,
                    beforeSend: function() {
                        $("#error_name").html(' ');
                        $(".save").html(`<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> {{ __('message.Wait') }}`);
                        $(".save").attr('disabled', true);
                    },
                    success: function(response) {
                        $(".save").html("{{ __('message.Submit') }}");
                        $(".save").attr('disabled', false);
                        if (response.server_error && response.status == false) {
                            toastr.error("{{ __('message.Something went wrong. Please try again.') }}", "{{ __('message.Error') }}");
                        } else if (response.status == false) {
                            $.each(response.errors, function(key, value) {
                                $('#error_' + key).html('<p class="text-danger mb-0">' + value + '</p>');
                            });
                            toastr.warning("{{ __('message.Please input proper data.') }}", "{{ __('message.Warning') }}");
                        } else {
                            $('#trading_form')[0].reset();
                            // toastr.success("{{ __('message.Saved successfully.') }}", "{{ __('message.Success') }}");
                            toastr.success(response.message, "{{ __('message.Success') }}");
                            setTimeout(function() {
                                location.href = response.data;
                            }, 2000);
                        }
                    }
                });
            } else {
                return false;
            }
        });

        $("#res_form").validate({
            rules: {
                res_lead_master_id: {
                    required: true
                },
                res_name: {
                    required: true
                },
                res_mobile: {
                    required: true,
                    minlength: 10,
                    regex: "[6-7-8-9]{1}[0-9]{9}"
                },
                res_address: {
                    required: true
                },
                "res_penal_company_id[]": {
                    required: true
                },
                res_penal_type_id: {
                    required: true
                },
                res_penal_watt_id: {
                    required: true
                },
                res_penal_nos: {
                    required: true
                },
                res_pv_capacity_kw: {
                    required: true
                },
                "res_inveter_company_id[]": {
                    required: true
                },
                res_inveter_capacity: {
                    required: true
                },
                res_no_of_inveter: {
                    required: true
                },
                res_structure: {
                    required: true
                },
                res_total_system_cost: {
                    required: true
                },
                res_meter_charges: {
                    required: true
                },
                res_agent_sales_person_id: {
                    required: true
                },
                // res_registration_fee: {
                //     required: true
                // },
            },
            messages: {
                res_lead_master_id: "Please Select Lead",
                res_name: "{{ __('message.Enter Name') }}",
                res_mobile: {
                    required: "{{ __('message.Enter Mobile Number') }}",
                    minlength: "{{ __('message.Enter at least 10 digits') }}",
                    regex: "{{ __('message.Enter Valid Number') }}"
                },
                res_address: "{{ __('message.Enter Address') }}",
                "res_penal_company_id[]": "{{ __('message.Select Panel Company Name') }}",
                res_penal_type_id: "{{ __('message.Select Panel Type') }}",
                res_penal_watt_id: "{{ __('message.Select Panel Watt') }}",
                res_penal_nos: "{{ __('message.Enter Panel Nos') }}",
                res_pv_capacity_kw: "{{ __('message.Enter PV Capacity Kw') }}",
                "res_inveter_company_id[]": "{{ __('message.Select Inveter Company Name') }}",
                res_inveter_capacity: "{{ __('message.Enter Inveter Capacity') }}",
                res_no_of_inveter: "{{ __('message.Enter No Of Inveter') }}",
                res_structure: "{{ __('message.Enter Structure') }}",
                res_total_system_cost: "{{ __('message.Enter Total System Cost') }}",
                res_meter_charges: "{{ __('message.Enter Meter Charges') }}",
                res_agent_sales_person_id: "Select Agent Sales Person",
                // res_registration_fee: "{{ __('message.Enter Registration Fee') }}",
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
        $(document).on('click', '.save_res', function(event) {
            event.preventDefault();


            if ($("#res_form").valid()) {
                var formData = new FormData($("#res_form")[0]);
                $.ajax({
                    type: "POST",
                    url: "{{route('sales-quatation.store')}}",
                    data: formData,
                    dataType: 'json',
                    cache: false,
                    contentType: false,
                    processData: false,
                    beforeSend: function() {
                        $("#error_name").html(' ');
                        $(".save").html(`<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> {{ __('message.Wait') }}`);
                        $(".save").attr('disabled', true);
                    },
                    success: function(response) {
                        $(".save").html("{{ __('message.Submit') }}");
                        $(".save").attr('disabled', false);
                        if (response.server_error && response.status == false) {
                            toastr.error("{{ __('message.Something went wrong. Please try again.') }}", "{{ __('message.Error') }}");
                        } else if (response.status == false) {
                            $.each(response.errors, function(key, value) {
                                $('#error_' + key).html('<p class="text-danger mb-0">' + value + '</p>');
                            });
                            toastr.warning("{{ __('message.Please input proper data.') }}", "{{ __('message.Warning') }}");
                        } else {
                            $('#res_form')[0].reset();
                            toastr.success(response.message, "{{ __('message.Success') }}");
                            setTimeout(function() {
                                location.href = response.data;
                            }, 2000);
                        }
                    }
                });
            } else {
                return false;
            }
        });
        $("#roofdataform").validate({
            rules: {
                roof_lead_master_id: {
                    required: true
                },
                roof_name: {
                    required: true
                },
                roof_mobile: {
                    required: true,
                    minlength: 10,
                    regex: "[6-7-8-9]{1}[0-9]{9}"
                },
                roof_address: {
                    required: true
                },
                "penal_company_id[]": {
                    required: true
                },
                penal_type_id: {
                    required: true
                },
                penal_watt_id: {
                    required: true
                },
                penal_nos: {
                    required: true
                },
                pv_capacity_kw: {
                    required: true
                },
                "inveter_company_id[]": {
                    required: true
                },
                inveter_capacity: {
                    required: true
                },
                no_of_inveter: {
                    required: true
                },
                structure: {
                    required: true
                },
                meter_charges: {
                    required: true
                },
                registration_fee: {
                    required: true
                },
                rate_per_kw: {
                    required: true
                },
                quatation_type: {
                    required: true
                },
                roof_agent_sales_person_id: {
                    required: true
                },
            },
            messages: {
                roof_lead_master_id: "Please Select Lead",
                roof_name: "{{ __('message.Enter Name') }}",
                roof_mobile: {
                    required: "{{ __('message.Enter Mobile Number') }}",
                    minlength: "{{ __('message.Enter at least 10 digits') }}",
                    regex: "{{ __('message.Enter Valid Number') }}"
                },
                roof_address: "{{ __('message.Enter Address') }}",
                "penal_company_id[]": "{{ __('message.Select Panel Company Name') }}",
                penal_type_id: "{{ __('message.Select Panel Type') }}",
                penal_watt_id: "{{ __('message.Select Panel Watt') }}",
                penal_nos: "{{ __('message.Enter Panel Nos') }}",
                pv_capacity_kw: "{{ __('message.Enter PV Capacity Kw') }}",
                "inveter_company_id[]": "{{ __('message.Select Inveter Company Name') }}",
                inveter_capacity: "{{ __('message.Enter Inveter Capacity') }}",
                no_of_inveter: "{{ __('message.Enter No Of Inveter') }}",
                structure: "{{ __('message.Enter Structure') }}",
                meter_charges: "{{ __('message.Enter Meter Charges') }}",
                registration_fee: "{{ __('message.Enter Registration Fee') }}",
                rate_per_kw: "{{ __('message.Enter Rate Per KW') }}",
                quatation_type: "{{ __('message.Select Quatation Type') }}",
                roof_agent_sales_person_id: "Select Agent Sales Person",

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
        $(document).on('click', '.save_roof', function(event) {
            event.preventDefault();


            if ($("#roofdataform").valid()) {
                var formData = new FormData($("#roofdataform")[0]);
                $.ajax({
                    type: "POST",
                    url: "{{route('sales-quatation.store')}}",
                    data: formData,
                    dataType: 'json',
                    cache: false,
                    contentType: false,
                    processData: false,
                    beforeSend: function() {
                        $("#error_name").html(' ');
                        $(".save").html(`<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> {{ __('message.Wait') }}`);
                        $(".save").attr('disabled', true);
                    },
                    success: function(response) {
                        $(".save").html("{{ __('message.Submit') }}");
                        $(".save").attr('disabled', false);
                        if (response.server_error && response.status == false) {
                            toastr.error("{{ __('message.Something went wrong. Please try again.') }}", "{{ __('message.Error') }}");
                        } else if (response.status == false) {
                            $.each(response.errors, function(key, value) {
                                $('#error_' + key).html('<p class="text-danger mb-0">' + value + '</p>');
                            });
                            toastr.warning("{{ __('message.Please input proper data.') }}", "{{ __('message.Warning') }}");
                        } else {
                            $('#roofdataform')[0].reset();
                            // toastr.success("{{ __('message.Saved successfully.') }}", "{{ __('message.Success') }}");
                            toastr.success(response.message, "{{ __('message.Success') }}");
                            setTimeout(function() {
                                location.href = response.data;
                            }, 2000);
                        }
                    }
                });
            } else {
                return false;
            }
        });

        $('.form_type').on('change', function() {
            formType();
        });

       function formType() {
                var Value = $('.form_type:checked').val();
                if (Value == 'trading') {

                    const urlParams = new URLSearchParams(window.location.search);
                    let leadId = urlParams.get('id');

                    if (leadId) {
                        $("#lead_master_id").val(leadId).trigger('change');
                    }

                    $("#res_form").addClass('d-none');
                    $("#roofdataform").addClass('d-none');
                    $("#trading_form").removeClass('d-none');
                } else if (Value == 'resident') {
                    const urlParams = new URLSearchParams(window.location.search);
                    let leadId = urlParams.get('id');
                    if (leadId) {
                        $("#res_lead_master_id").val(leadId).trigger('change');
                    }
                    $("#trading_form").addClass('d-none');
                    $("#res_form").removeClass('d-none');
                    $("#roofdataform").addClass('d-none');
                } else if (Value == 'roof') {


                    const urlParams = new URLSearchParams(window.location.search);
                    let leadId = urlParams.get('id');

                    if (leadId) {
                        $("#roof_lead_master_id").val(leadId).trigger('change');
                    }

                    $("#res_form").addClass('d-none');
                    $("#trading_form").addClass('d-none');
                    $("#roofdataform").removeClass('d-none');
                }
            }
    });
    $('.custom-select2').on('change', function() {
        var formType = $('.form_type:checked').val();
        var element = $(this).attr('name');
        if (formType == 'trading') {
            $('#trading_form').validate().showErrors({
                [element]: ''
            });
        }
        if (formType == 'resident') {
            $('#res_form').validate().showErrors({
                [element]: ''
            });
        }
        if (formType == 'roof') {
            $('#roofdataform').validate().showErrors({
                [element]: ''
            });
        }
    });

    $(document).on('change', '.type', function() {

        if ($(this).val() == 'Item') {
            $(this).parent().parent().parent().find('.type-item-group').addClass('d-none');
            $(this).parent().parent().parent().find('.type-item').removeClass('d-none');
        } else {
            $(this).parent().parent().parent().find('.type-item-group').removeClass('d-none');
            $(this).parent().parent().parent().find('.type-item').addClass('d-none');
        }

    });
</script>
@endsection
