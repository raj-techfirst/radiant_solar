@if(!is_null($rateCalculator))
<div class="row">
    <div class="col-12 col-md-6 col-lg-4 mb-1 custom-input-group">
        Mobile : {{ $rateCalculator->mobile }}
    </div>
    <div class="col-12 col-md-6 col-lg-4 mb-1 custom-input-group">
        Name : {{ $rateCalculator->name }}
    </div>
    <div class="col-12 col-md-6 col-lg-2 mb-1 custom-input-group">
        PV Capacity KW : {{ $rateCalculator->pv_capacity_kw }}
    </div>
</div>
<div class="row">
    <div class="col-12 col-md-12 col-lg-12">

        <table class="table table-bordered table-sm table-hover table-striped calc-table">
            <thead>
                <tr>
                    <th class="text-center">#</th>
                    <th style="min-width:400px;">Product</th>
                    <th class="text-center" style="min-width:60px;">Unit</th>
                    <th class="text-center" style="min-width:40px;">WPK</th>
                    <th class="text-center" style="min-width:60px;">Qty</th>
                    <th class="text-end" style="min-width:70px;">Rate/Unit</th>
                    <th class="text-end" style="min-width:70px;">Rate</th>
                    <th class="text-end" style="min-width:40px;">GST%</th>
                    <th class="text-end" style="min-width:60px;">GST Amount</th>
                    <th class="text-end" style="min-width:80px;">Total</th>
                    <th class="text-end" style="min-width:40px;">Per Watt</th>
                </tr>
            </thead>
            <tbody id="itemsContainer">
                @if($rateCalculatorMeta->count() > 0)
                @php $c = 1; $cat = '';
                $totalBomRate = $totalBomGst = $totalBomTotal = $totalBomPerWatt = 0;
                $totalOtherRate = $totalOtherGst = $totalOtherTotal = $totalOtherPerWatt = 0;
                @endphp
                @foreach($rateCalculatorMeta as $key => $value)

                @if($value->category != "sales" && $value->category != "Others & Documentation Charge")
                @php
                $totalBomRate += $value->subtotal;
                $totalBomGst += $value->totalTax;
                $totalBomTotal += $value->total;
                $totalBomPerWatt += $value->per_watt;
                @endphp
                @endif

                @if($value->category == "Others & Documentation Charge")
                @php
                $totalOtherRate += $value->subtotal;
                $totalOtherGst += $value->totalTax;
                $totalOtherTotal += $value->total;
                $totalOtherPerWatt += $value->per_watt;
                @endphp
                @endif

                @if($cat != "Others & Documentation Charge" && $value->category == "Others & Documentation Charge")
                <tr style="--bs-table-accent-bg:rgba(153, 153, 153, 0.38) !important;color: #737373;">
                    <th colspan="6" class="text-end">Total BOM</th>
                    <th class="text-end" style="padding-right:5px !important">₹ {{ number_format($totalBomRate,2) }}</th>
                    <th></th>
                    <th class="text-end" style="padding-right:5px !important">₹ {{ number_format($totalBomGst,2) }}</th>
                    <th class="text-end" style="padding-right:5px !important">₹ {{ number_format($totalBomTotal,2) }}</th>
                    <th class="text-end" style="padding-right:5px !important">₹ {{ number_format($totalBomPerWatt,2) }}</th>
                </tr>
                @php $c=1; @endphp
                @endif
                @if($value->category == "sales")
                <tr style="--bs-table-accent-bg:rgba(153, 153, 153, 0.38) !important;color: #737373;">
                    <th colspan="6" class="text-end">Total Other</th>
                    <th class="text-end" style="padding-right:5px !important">₹ {{ number_format($totalOtherRate,2) }}</th>
                    <th></th>
                    <th class="text-end" style="padding-right:5px !important">₹ {{ number_format($totalOtherGst,2) }}</th>
                    <th class="text-end" style="padding-right:5px !important">₹ {{ number_format($totalOtherTotal,2) }}</th>
                    <th class="text-end" style="padding-right:5px !important">₹ {{ number_format($totalOtherPerWatt,2) }}</th>
                </tr>
                <tr style="--bs-table-accent-bg:rgba(80, 80, 80, 0.38) !important;color: #000000;font-size:16px !important;">
                    <th colspan="6" class="text-end">Total Costing</th>
                    <th class="text-end" style="padding-right:5px !important">₹ {{ number_format($rateCalculator->totalRate,2) }}</th>
                    <th></th>
                    <th class="text-end" style="padding-right:5px !important">₹ {{ number_format($rateCalculator->gst_amount,2) }}</th>
                    <th class="text-end" style="padding-right:5px !important">₹ {{ number_format($rateCalculator->total_amount,2) }}</th>
                    <th class="text-end" style="padding-right:5px !important">₹ {{ number_format($rateCalculator->per_watt,2) }}</th>
                </tr>
                @php $c=1; @endphp
                @endif
                @if($cat != $value->category && $value->category != 'sales')
                <tr style="--bs-table-accent-bg: #ff790061 !important;color: #000000;">
                    <th colspan="11"><b>{{ ucfirst($value->category) }}</b></th>
                </tr>
                @endif
                <tr>
                    <td>{{ $c }}</td>
                    <td>{{ $value->item }}</td>
                    <td class="text-center">{{ $value->unit }}</td>
                    <td class="text-center">{{ $value->wpk }}</td>
                    <td class="text-center">{{ $value->qty }}</td>
                    <td class="text-end" style="padding-right:5px !important"> ₹ {{ number_format($value->rate,2) }}</td>
                    <td class="text-end" style="padding-right:5px !important"> ₹ {{ number_format($value->subtotal,2) }}</td>
                    <td class="text-end" style="padding-right:5px !important"> {{ number_format($value->taxRate,2) }}</td>
                    <td class="text-end" style="padding-right:5px !important"> ₹ {{ number_format($value->totalTax,2)}}</td>
                    <td class="text-end" style="padding-right:5px !important"> ₹ {{ number_format($value->total,2)}}</td>
                    <td class="text-end" style="padding-right:5px !important"> ₹ {{ number_format($value->per_watt,2)}}</td>
                </tr>
                @php $c++; $cat = $value->category; @endphp
                @endforeach
                @endif
                <tr style="--bs-table-accent-bg:rgba(26, 173, 58, 0.38) !important;color: #000000;font-size:16px !important;">
                    <th colspan="6" class="text-end"> Profit </th>
                    <th class="text-end" style="padding-right:5px !important">₹ {{ number_format($rateCalculator->profit_totalRate,2) }}</th>
                    <th></th>
                    <th class="text-end" style="padding-right:5px !important">₹ {{ number_format($rateCalculator->profit_gst_amount,2) }}</th>
                    <th class="text-end" style="padding-right:5px !important">₹ {{ number_format($rateCalculator->profit_total_amount,2) }}</th>
                    <th class="text-end" style="padding-right:5px !important">₹ {{ number_format($rateCalculator->profit_per_watt,2) }}</th>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<div class="row">
    <div class="col-12 col-md-12 col-lg-12 mt-2">
        <div class="w-100 mb-2">
            <label class="form-label" for="remarks">Remarks : {{ $rateCalculator->remarks }}</label>

        </div>
        <div class="d-flex w-100">
            <div class="text-start w-50"><button type="button" class="btn btn-secondary"> Cancel</button></div>
        </div>

    </div>
</div>
@endif