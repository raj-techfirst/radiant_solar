@extends('layouts.app')
@section('title', 'Calculator')

@section('content')
<style>
    .calc-table tr td,
    .calc-table tr th,
    .calc-table tr td input {
        padding: 3px;
    }
</style>
<div class="row">
    <div class="col-12 mb-1">
        <h4 class="content-header-title float-start">Calculator</h4>
    </div>
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form id="form" method="post">
                    @csrf
                    <!-- Container for dynamic rows -->
                    <div class="row">
                        <div class="col-12 col-md-6 col-lg-3 mb-1 custom-input-group">
                            <label class="form-label" for="mobile">Mobile <span class="text-danger">*</span></label>
                            <input type="number" maxlength="10" class="form-control" name="mobile" id="mobile" placeholder="Mobile No." required>
                            <span class="invalid-feedback d-block" id="error_mobile" role="alert"></span>
                        </div>
                        <div class="col-12 col-md-6 col-lg-5 mb-1 custom-input-group">
                            <label class="form-label" for="name">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" id="name" placeholder="Name" value="" oninput="this.value = this.value.toUpperCase()">
                            <span class="invalid-feedback d-block" id="error_name" role="alert"></span>
                        </div>
                        <div class="col-12 col-md-3 col-lg-2 mb-1 custom-input-group ">
                            <label class="form-label" for="bom_id">BOM</label>
                            <select class="form-select select2" name="bom_id" id="bom_id">
                                <option value="0">None</option>
                                @foreach($boms as $bomValue)
                                <option value="{{ $bomValue->id }}" {{ (isset($salesMaster) && $bomValue->id == $salesMaster->bom_id) ? 'selected' : '' }}>{{ $bomValue->bom_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-3 col-lg-2 mb-1 custom-input-group">
                            <label class="form-label" for="res_pv_capacity_kw">PV Capacity Kw <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="res_pv_capacity_kw" id="res_pv_capacity_kw" placeholder="PV Capacity Kw" value="" readonly>
                            <span class="invalid-feedback d-block" id="error_res_pv_capacity_kw" role="alert"></span>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 col-md-12 col-lg-12">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover table-striped calc-table d-none">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th style="min-width:400px;">Product</th>
                                            <th style="min-width:60px;">Unit</th>
                                            <th style="min-width:60px;">WPK</th>
                                            <th style="min-width:60px;">Qty</th>
                                            <th style="min-width:80px;">Rate/Unit</th>
                                            <th style="min-width:90px;">Rate</th>
                                            <th style="min-width:60px;">GST%</th>
                                            <th style="min-width:80px;">GST Amount</th>
                                            <th style="min-width:100px;">Total</th>
                                            <th style="min-width:60px;">Per Watt</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody id="itemsContainer">

                                    </tbody>
                                    <tbody>
                                        <!-- <tr>
                                            <td colspan="12" class="text-end"><button type="button" class="btn btn-link text-success" onclick="addItemRow()"> <i class="fas fa-plus"> </i> Add Item</button></td>
                                        </tr> -->
                                        <tr style="--bs-table-accent-bg:rgba(153, 153, 153, 0.38) !important;color: #737373;">
                                            <th colspan="6" class="text-end">Total BOM</th>
                                            <th class="text-end" id="grandTotalRate"></th>
                                            <th></th>
                                            <th class="text-end" id="grandTotalTax"></th>
                                            <th class="text-end" id="grandTotalAmount"></th>
                                            <th class="text-end" id="grandTotalperWatt"></th>
                                            <th></th>
                                        </tr>
                                        <tr>
                                            <td colspan="12" class="p-2"></td>
                                        </tr>
                                    </tbody>

                                    <tbody id="otheritemsContainer">

                                    </tbody>
                                    <tbody>
                                        <tr>
                                            <td colspan="12" class="text-end"><a href="javascript:void(0);" class="text-success add-other-new"> <i class="fas fa-plus"> </i> Add Item</a></td>
                                        </tr>
                                        <tr style="--bs-table-accent-bg:rgba(153, 153, 153, 0.38) !important;color:#737373;">
                                            <th colspan="6" class="text-end">Total Other</th>
                                            <th class="text-end" id="othergrandTotalRate"></th>
                                            <th></th>
                                            <th class="text-end" id="othergrandTotalTax"></th>
                                            <th class="text-end" id="othergrandTotalAmount"></th>
                                            <th class="text-end" id="othergrandTotalperWatt"></th>
                                            <th></th>
                                        </tr>
                                    </tbody>
                                    <tbody>
                                        <tr style="--bs-table-accent-bg:rgba(80, 80, 80, 0.38) !important;color: #000000;font-size:16px !important;">
                                            <th colspan="6" class="text-end">Total Costing</th>
                                            <th class="text-end" id="costingTotalRate"></th>
                                            <th></th>
                                            <th class="text-end" id="costingTotalTax"></th>
                                            <th class="text-end" id="costingTotalAmount"></th>
                                            <th class="text-end" id="costingTotalperWatt"></th>
                                            <th></th>
                                        </tr>
                                    </tbody>
                                    <tbody id="saleitemsContainer">

                                    </tbody>
                                    <tbody id="profititemsContainer">
                                        <tr style="--bs-table-accent-bg:rgba(26, 173, 58, 0.38) !important;color: #000000;font-size:16px !important;">
                                            <th colspan="6" class="text-end">Profit</th>
                                            <th class="text-end" id="profitTotalRate"></th>
                                            <th></th>
                                            <th class="text-end" id="profitTotalTax"></th>
                                            <th class="text-end" id="profitTotalAmount"></th>
                                            <th class="text-end" id="profitTotalperWatt"></th>
                                            <th></th>
                                        </tr>
                                    </tbody>
                                </table>
                                <div class="rate-loader"></div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 col-md-12 col-lg-12 mt-2">
                            <div class="w-100 mb-2">
                                <label class="form-label" for="remarks">Remarks</label>
                                <input type="text" class="form-control" name="remarks" id="remarks" placeholder="If Any">
                            </div>
                            <div class="d-flex w-100">
                                <div class="text-start w-50"><button type="button" class="btn btn-secondary"> Cancel</button></div>
                                <div class="text-end w-50"><button type="submit" class="btn btn-success  save "> <i class="fas fa-save"> </i> Save</button></div>
                            </div>

                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@section('pagescript')
<script type="application/javascript">
    let newRowCount = 2001;
    let rowCount = 0;
    let otherrowCount = 0;

    $(document).ready(function() {

        $("#form").validate({
            rules: {
                mobile: {
                    required: true,
                },
                res_pv_capacity_kw: {
                    required: true,
                }
            },
            messages: {
                mobile: {
                    required: "Enter mobile"
                },
                res_pv_capacity_kw: {
                    required: "Enter PV Capacity KW"
                }
            },
            errorElement: "p",
            errorClass: "text-danger mb-0 custom-error",

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
    });

    function calculateTotalOther() {
        $('.other-item-row').each(function() {
            const $row = $(this);
            const otherrowIndex = $row.data('row-index');
            const wpk = parseFloat($row.find(`#otherwpk-${otherrowIndex}`).val()) || 0;
            const qty = parseFloat($row.find(`#otherqty-${otherrowIndex}`).val()) || 0;
            const rate = parseFloat($row.find(`#otherrate-${otherrowIndex}`).val()) || 0;
            const taxRate = parseFloat($row.find(`#othertaxRate-${otherrowIndex}`).val()) || 0;

            let subTotal = 0;
            subTotal = (qty * rate);

            const totalTax = (subTotal * taxRate) / 100;
            const totalAmount = subTotal + totalTax;

            let perWatt = 0;
            const pvCapacityKW = parseFloat($(`#res_pv_capacity_kw`).val()) || 0;
            if (pvCapacityKW !== 0) {
                perWatt = (subTotal / pvCapacityKW) / 1000;
            }

            $row.find(`#othertotalTax-${otherrowIndex}`).val(totalTax.toFixed(2));
            $row.find(`#othertotal-${otherrowIndex}`).val(totalAmount.toFixed(2));
            $row.find(`#othersubtotal-${otherrowIndex}`).val(subTotal.toFixed(2));
            $row.find(`#otherper-watt-${otherrowIndex}`).val(perWatt.toFixed(2));
        });

        updateGrandTotalsOther();
    }

    // Calculate the totals for each row

    function calculateTotalSale(otherrowIndex, type = 1) {
        const $row = $(`.sale-item-row[data-row-index="${otherrowIndex}"]`);

        const qty = parseFloat($row.find(`#saleqty-${otherrowIndex}`).val()) || 0;
        const rate = parseFloat($row.find(`#salerate-${otherrowIndex}`).val()) || 0;
        const totalAmount = parseFloat($row.find(`#saletotal-${otherrowIndex}`).val()) || 0;
        const subTotal = parseFloat($row.find(`#salesubtotal-${otherrowIndex}`).val()) || 0;
        const taxRate = parseFloat($row.find(`#saletaxRate-${otherrowIndex}`).val()) || 0;

        let finalRate = rate;
        let finalSubTotal = subTotal;
        let finalTotalAmount = totalAmount;
        let totalTax = 0;
        let perWatt = 0;
        const pvCapacityKW = parseFloat($(`#res_pv_capacity_kw`).val()) || 0;

        // Case 1: If Rate is Provided
        if (type == 1) {
            finalSubTotal = qty * rate;
            totalTax = (finalSubTotal * taxRate) / 100;
            finalTotalAmount = finalSubTotal + totalTax;
        }
        // Case 2: If Total Amount is Provided
        else if (type == 2) {
            finalRate = finalSubTotal / qty;
            totalTax = finalSubTotal * taxRate / 100;
            finalTotalAmount = finalSubTotal + totalTax;

        }
        // Case 3: If SubTotal is Provided
        else if (type == 3) {
            finalSubTotal = (finalTotalAmount * 100) / (100 + taxRate);
            finalRate = finalSubTotal / qty;
            totalTax = finalSubTotal * taxRate / 100;
        }

        // Calculate Per Watt if applicable
        if (pvCapacityKW !== 0) {
            perWatt = (finalSubTotal / pvCapacityKW) / 1000; // Convert to per watt
        }

        // Update the fields with the calculated values
        if (type != 1) {
            $row.find(`#salerate-${otherrowIndex}`).val(finalRate.toFixed(2));
        }

        if (type != 3) {
           $row.find(`#saletotal-${otherrowIndex}`).val(finalTotalAmount.toFixed(2));
        }

        if (type != 2) {
            $row.find(`#salesubtotal-${otherrowIndex}`).val(finalSubTotal.toFixed(2));
        }

        $row.find(`#saletotalTax-${otherrowIndex}`).val(totalTax.toFixed(2));
        $row.find(`#saleper-watt-${otherrowIndex}`).val(perWatt.toFixed(2));

        // Call the function to update grand totals if needed
        updateGrandTotalsOther();
    }
    
    // Update the grand totals (Tax, Total)
    function updateGrandTotalsOther() {
        let othergrandTotalQty = 0;
        let othergrandTotalRate = 0;
        let othergrandTotalTax = 0;
        let othergrandTotalAmount = 0;
        let othergrandTotalperWatt = 0;

        $('.other-item-row').each(function() {
            const rowIndex = $(this).data('row-index');
            const qty = parseFloat($(`#otherqty-${rowIndex}`).val()) || 0;
            const totalRate = parseFloat($(`#othersubtotal-${rowIndex}`).val()) || 0;
            const totalTax = parseFloat($(`#othertotalTax-${rowIndex}`).val()) || 0;
            const totalAmount = parseFloat($(`#othertotal-${rowIndex}`).val()) || 0;
            const totalperWatt = parseFloat($(`#otherper-watt-${rowIndex}`).val()) || 0;

            othergrandTotalQty += qty;
            othergrandTotalRate += totalRate;
            othergrandTotalTax += totalTax;
            othergrandTotalAmount += totalAmount;
            othergrandTotalperWatt += totalperWatt;
        });


        //$('#grandTotalQty').text(grandTotalQty.toFixed(2));
        $('#othergrandTotalRate').text(othergrandTotalRate.toFixed(2));
        $('#othergrandTotalTax').text(othergrandTotalTax.toFixed(2));
        $('#othergrandTotalAmount').text(othergrandTotalAmount.toFixed(2));
        $('#othergrandTotalperWatt').text(othergrandTotalperWatt.toFixed(2));

        updateCostingTotals();
    }

    // Calculate the totals for each row
    function calculateTotal(rowIndex) {
        const $row = $(`.item-row[data-row-index="${rowIndex}"]`);
        let special = $row.attr(`data-row-special`);

        const wpk = parseFloat($row.find(`#wpk-${rowIndex}`).val()) || 0;
        const qty = parseFloat($row.find(`#qty-${rowIndex}`).val()) || 0;
        const rate = parseFloat($row.find(`#rate-${rowIndex}`).val()) || 0;
        const taxRate = parseFloat($row.find(`#taxRate-${rowIndex}`).val()) || 0;

        if (special === 'panel') {
            pvCapacityKW = (wpk * qty * 0.001);
        }
        $(`#res_pv_capacity_kw`).val(pvCapacityKW.toFixed(2));

        if (special === "panel") {
            $(`.item-row[data-row-autoKw="1"]`).find('[id ^= "qty-"]').val(pvCapacityKW.toFixed(2));
            $(`.other-item-row[data-row-autoKw="1"]`).find('[id ^= "otherqty-"]').val(pvCapacityKW.toFixed(2));
            $(`.sale-item-row[data-row-autokw="1"]`).find('[id ^= "saleqty-"]').val(pvCapacityKW.toFixed(2));
        }

        let subTotal = 0;
        if (special === "panel") { // || special === "Structure"
            subTotal = (wpk * qty * rate);
        } else {
            subTotal = (qty * rate);
        }

        const totalTax = (subTotal * taxRate) / 100;
        const totalAmount = subTotal + totalTax;

        let perWatt = 0;
        pvCapacityKW = parseFloat($(`#res_pv_capacity_kw`).val()) || 0;
        if (pvCapacityKW !== 0) {
            perWatt = (subTotal / pvCapacityKW) / 1000;
        }

        $row.find(`#totalTax-${rowIndex}`).val(totalTax.toFixed(2));
        $row.find(`#total-${rowIndex}`).val(totalAmount.toFixed(2));
        $row.find(`#subtotal-${rowIndex}`).val(subTotal.toFixed(2));
        $row.find(`#per-watt-${rowIndex}`).val(perWatt.toFixed(2));

        updateGrandTotals();
    }

    // Update the grand totals (Tax, Total)
    function updateGrandTotals() {
        grandTotalQty = 0;
        grandTotalTax = 0;
        grandTotalAmount = 0;
        grandTotalperWatt = 0;
        grandTotalRate = 0;

        $('.item-row').each(function() {
            const rowIndex = $(this).data('row-index');


            const qty = parseFloat($(`#qty-${rowIndex}`).val()) || 0;
            const totalRate = parseFloat($(`#subtotal-${rowIndex}`).val()) || 0;
            const totalTax = parseFloat($(`#totalTax-${rowIndex}`).val()) || 0;
            const totalAmount = parseFloat($(`#total-${rowIndex}`).val()) || 0;
            const totalperWatt = parseFloat($(`#per-watt-${rowIndex}`).val()) || 0;

            grandTotalQty += qty;
            grandTotalTax += totalTax;
            grandTotalAmount += totalAmount;
            grandTotalperWatt += totalperWatt;
            grandTotalRate += totalRate;
        });


        //$('#grandTotalQty').text(grandTotalQty.toFixed(2));
        $('#grandTotalRate').text(grandTotalRate.toFixed(2));
        $('#grandTotalTax').text(grandTotalTax.toFixed(2));
        $('#grandTotalAmount').text(grandTotalAmount.toFixed(2));
        $('#grandTotalperWatt').text(grandTotalperWatt.toFixed(2));

        updateCostingTotals();
    }

    // Update Costing (Tax, Total)
    function updateCostingTotals() {
        costingTotalTax = 0;
        costingTotalAmount = 0;
        costingTotalperWatt = 0;
        costingTotalRate = 0;

        costingTotalRate += parseFloat($(`#grandTotalRate`).html()) || 0;
        costingTotalTax += parseFloat($(`#grandTotalTax`).html()) || 0;
        costingTotalAmount += parseFloat($(`#grandTotalAmount`).html()) || 0;
        costingTotalperWatt += parseFloat($(`#grandTotalperWatt`).html()) || 0;

        costingTotalRate += parseFloat($(`#othergrandTotalRate`).html()) || 0;
        costingTotalTax += parseFloat($(`#othergrandTotalTax`).html()) || 0;
        costingTotalAmount += parseFloat($(`#othergrandTotalAmount`).html()) || 0;
        costingTotalperWatt += parseFloat($(`#othergrandTotalperWatt`).html()) || 0;

        $('#costingTotalRate').text(costingTotalRate.toFixed(2));
        $('#costingTotalTax').text(costingTotalTax.toFixed(2));
        $('#costingTotalAmount').text(costingTotalAmount.toFixed(2));
        $('#costingTotalperWatt').text(costingTotalperWatt.toFixed(2));

        updateProfitTotals();
    }


    // Update Costing (Tax, Total)
    function updateProfitTotals() {

        let profitTotalRate = parseFloat($(`#salesubtotal-90001`).val()) || 0;
        let profitTotalTax = parseFloat($(`#saletotalTax-90001`).val()) || 0;
        let profitTotalAmount = parseFloat($(`#saletotal-90001`).val()) || 0;
        let profitTotalperWatt = parseFloat($(`#saleper-watt-90001`).val()) || 0;

        profitTotalRate -= parseFloat($(`#costingTotalRate`).html()) || 0;
        profitTotalTax -= parseFloat($(`#costingTotalTax`).html()) || 0;
        profitTotalAmount -= parseFloat($(`#costingTotalAmount`).html()) || 0;
        profitTotalperWatt -= parseFloat($(`#costingTotalperWatt`).html()) || 0;

        $('#profitTotalRate').text(profitTotalRate.toFixed(2));
        $('#profitTotalTax').text(profitTotalTax.toFixed(2));
        $('#profitTotalAmount').text(profitTotalAmount.toFixed(2));
        $('#profitTotalperWatt').text(profitTotalperWatt.toFixed(2));
    }

    // Add a new item row to the table
    $(document).on("click", ".add-other-new", function() {

        const otherrowIndex = otherrowCount;
        const otherrowHTML = `<tr class="other-item-row" data-row-index="${otherrowIndex}" data-row-special="" data-row-autoKw="">
                                <td>${otherrowIndex + 1}
                                    <input type="hidden" name="is_special[]" value="" />
                                    <input type="hidden" name="autoKw[]" value="" />
                                    <input type="hidden" name="category[]" value="Others & Documentation Charge" />
                                </td>
                                <td colspan="2"><input type="text" id="otheritem-${otherrowIndex}" name="item[]" class="form-control" value=""></td>               
                                <td><input type="text" id="otherwpk-${otherrowIndex}" name="wpk[]" class="form-control" value="0" readonly></td>
                                <td><input type="number" id="otherqty-${otherrowIndex}" name="qty[]" class="form-control" value="" oninput="calculateTotalOther(${otherrowIndex})"></td>
                                <td><input type="number" id="otherrate-${otherrowIndex}" name="rate[]" class="form-control text-end" value="" oninput="calculateTotalOther(${otherrowIndex})"></td>
                                <td><input type="text" id="othersubtotal-${otherrowIndex}" name="subtotal[]" class="form-control text-end" value="0" readonly></td>
                                <td><input type="number" id="othertaxRate-${otherrowIndex}" name="taxRate[]" class="form-control text-end" value="" oninput="calculateTotalOther(${otherrowIndex})"></td>
                                <td><input type="text" id="othertotalTax-${otherrowIndex}" name="totalTax[]" class="form-control text-end" readonly></td>
                                <td><input type="text" id="othertotal-${otherrowIndex}" name="total[]" class="form-control text-end" readonly></td>
                                <td><input type="text" id="otherper-watt-${otherrowIndex}" name="per_watt[]" class="form-control text-end" value="0" readonly></td>
                                <td><a class="text-danger" href="javascript:otherRemoveItemRow(${otherrowIndex})"><i class="fas fa-trash"></i></a></td>
                                </tr>`;
        otherrowCount++;
        $(this).closest('tr').before(otherrowHTML);
        calculateTotalOther(otherrowIndex);

    });

    $(document).on("click", ".add-new-btn", function() {
        let ctg = $(this).data('cat');
        let is_readonly = 'readonly';
        if (ctg == "panel") {
            is_readonly = '';
        }
        const rowIndex = newRowCount;

        const rowHTML = `<tr class="item-row" data-row-index="${rowIndex}" data-row-special="${ctg}" data-row-autoKw="">
                <td>#
                    <input type="hidden" name="is_special[]" value="" />
                    <input type="hidden" name="autoKw[]" value="" />
                    <input type="hidden" name="category[]" value="${ctg}" />
                </td>
                <td><input type="text" id="item-${rowIndex}" name="item[]" class="form-control"></td>
                <td><input type="text" id="unit-${rowIndex}" name="unit[]" class="form-control"></td>
                <td><input type="text" id="wpk-${rowIndex}" name="wpk[]" class="form-control" value="" ${is_readonly} oninput="calculateTotal(${rowIndex})"></td>
                <td><input type="number" id="qty-${rowIndex}" name="qty[]" class="form-control" oninput="calculateTotal(${rowIndex})"></td>
                <td><input type="number" id="rate-${rowIndex}" name="rate[]" class="form-control text-end" oninput="calculateTotal(${rowIndex})"></td>
                <td><input type="text" id="subtotal-${rowIndex}" name="subtotal[]" class="form-control text-end" value="0" readonly></td>
                <td><input type="number" id="taxRate-${rowIndex}" name="taxRate[]" class="form-control text-end" oninput="calculateTotal(${rowIndex})"></td>
                <td><input type="text" id="totalTax-${rowIndex}" name="totalTax[]" class="form-control text-end" readonly></td>
                <td><input type="text" id="total-${rowIndex}" name="total[]" class="form-control text-end" readonly></td>
                <td><input type="text" id="per-watt-${rowIndex}" name="per_watt[]" class="form-control text-end" value="0" readonly></td>
                <td><a class="text-danger" href="javascript:removeItemRow(${rowIndex})"><i class="fas fa-trash"></i></a></td>
            </tr>`;
        newRowCount++;
        $(this).closest('tr').before(rowHTML);
        calculateTotal(rowIndex);
    });

    // Remove a row from the table
    function removeItemRow(rowIndex) {
        $(`.item-row[data-row-index="${rowIndex}"]`).remove();
        updateGrandTotals();
    }
    // Other Add a new item row to the table
    function otherAddItemRow() {
        const rowIndex = otherrowCount;
        const rowHTML = `<tr class="item-row" data-row-index="${rowIndex}" data-row-special="" data-row-autoKw="">
                <td>${rowIndex + 1}
                    <input type="hidden" name="is_special[]" value="" />
                    <input type="hidden" name="autoKw[]" value="" />
                    <input type="hidden" name="category[]" value="Others & Documentation Charge" />
                </td>
                <td colspan="4"><input type="text" id="otheritem-${rowIndex}" name="item[]" class="form-control"></td>
                <td><input type="text" id="otherwpk-${rowIndex}" name="wpk[]" class="form-control" value="0" readonly oninput="calculateTotalOther(${rowIndex})"></td>
                <td><input type="number" id="otherqty-${rowIndex}" name="qty[]" class="form-control" oninput="calculateTotalOther(${rowIndex})"></td>
                <td><input type="number" id="otherrate-${rowIndex}" name="rate[]" class="form-control" oninput="calculateTotalOther(${rowIndex})"></td>
                <td><input type="text" id="othersubtotal-${rowIndex}" name="subtotal[]" class="form-control" value="0" readonly></td>
                <td><input type="number" id="othertaxRate-${rowIndex}" name="taxRate[]" class="form-control" oninput="calculateTotalOther(${rowIndex})"></td>
                <td><input type="text" id="othertotalTax-${rowIndex}" name="totalTax[]" class="form-control" readonly></td>
                <td><input type="text" id="othertotal-${rowIndex}" name="total[]" class="form-control" readonly></td>
                <td><input type="text" id="otherper-watt-${rowIndex}" name="per_watt[]" class="form-control" value="0" readonly></td>
                <td><a class="text-danger" href="javascript:removeItemRow(${rowIndex})"><i class="fas fa-trash"></i></a></td>
            </tr>`;
        otherrowCount++;
        $('#otheritemsContainer').append(rowHTML);

        calculateTotalOther(rowIndex);
    }

    // Other Remove a row from the table
    function otherRemoveItemRow(rowIndex) {
        $(`.other-item-row[data-row-index="${rowIndex}"]`).remove();
        updateGrandTotalsOther();
    }

    $(document).on('click', '.save', function() {
        if ($("#form").valid()) {
            var formData = new FormData($("#form")[0]);
            $.ajax({
                type: "POST",
                url: "{{route('rate-calculator.store')}}",
                data: formData,
                dataType: 'json',
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: function() {
                    $("#error_name").html(' ');
                    $(".save").attr('disabled', true);
                },
                success: function(response) {
                    $(".save").attr('disabled', false);
                    if (response.server_error && response.status == false) {
                        toastr.error("Something went wrong. Please try again", "Error");
                    } else if (response.status == false) {
                        $.each(response.errors, function(key, value) {
                            $('#error_' + key).html('<p class="text-danger mb-0">' + value + '</p>');
                        });
                        toastr.warning("Please input proper data", "Warning");
                    } else {
                        $('#form')[0].reset();
                        toastr.success(response.message, "Success");
                        setTimeout(function() {
                            location.href = response.data;
                        }, 1000);
                    }
                }
            });
        } else {
            return false;
        }
    });

    $(document).on('change', '#bom_id', function() {
        rowCount = 0;
        otherrowCount = 0;
        let id = $(this).val();
        $.ajax({
            type: "POST",
            url: "{{route('get-rate-calc-bom-data')}}",
            data: {
                "id": id,
                "_token": "{{ csrf_token() }}",
            },
            dataType: 'json',
            beforeSend: function() {
                $(".calc-table").addClass('d-none');
                $(".rate-loader").html('Loading...');
                $("#itemsContainer").html('');
                $("#otheritemsContainer").html('');
                $('#saleitemsContainer').html('');
            },
            success: function(response) {
                $(".rate-loader").html('');
                $(".calc-table").removeClass('d-none');
                /* Othres */
                let otherCat = "";
                response.data.otheritemsOriginal.forEach(item => {
                    const otherrowIndex = otherrowCount;
                    let otherrowHTML = ``;
                    if (otherCat == "" || otherCat != item.category) {
                        otherrowHTML += `<tr style="--bs-table-accent-bg: #ff790061 !important;color: #000000;">
                                    <th colspan="12"><b>${item.category}</b></th>
                                </tr>`;
                    }
                    otherCat = item.category;
                    let is_readonly = 'readonly';

                    otherrowHTML += `<tr class="other-item-row" data-row-index="${otherrowIndex}" data-row-special="${item.is_special}" data-row-autoKw="${item.is_auto_kw}">
                            <input type="hidden" name="is_special[]" value="${item.is_special}" />
                            <input type="hidden" name="autoKw[]" value="${item.is_auto_kw}" />
                            <input type="hidden" name="category[]" value="${item.category}" />
                                <td>${otherrowIndex + 1}</td>
                                <td colspan="2"><input type="text" id="otheritem-${otherrowIndex}" name="item[]" class="form-control" value="${item.item}"></td>               
                                <td><input type="text" id="otherwpk-${otherrowIndex}" name="wpk[]" class="form-control" value="${item.wpk}" ${is_readonly} oninput="calculateTotalOther(${otherrowIndex})"></td>
                                <td><input type="number" id="otherqty-${otherrowIndex}" name="qty[]" class="form-control" value="${item.qty}" oninput="calculateTotalOther(${otherrowIndex})"></td>
                                <td><input type="number" id="otherrate-${otherrowIndex}" name="rate[]" class="form-control text-end" value="${item.rate}" oninput="calculateTotalOther(${otherrowIndex})"></td>
                                <td><input type="text" id="othersubtotal-${otherrowIndex}" name="subtotal[]" class="form-control text-end" value="0" readonly></td>
                                <td><input type="number" id="othertaxRate-${otherrowIndex}" name="taxRate[]" class="form-control text-end" value="${item.taxRate}" oninput="calculateTotalOther(${otherrowIndex})"></td>
                                <td><input type="text" id="othertotalTax-${otherrowIndex}" name="totalTax[]" class="form-control text-end" readonly></td>
                                <td><input type="text" id="othertotal-${otherrowIndex}" name="total[]" class="form-control text-end" readonly></td>
                                <td><input type="text" id="otherper-watt-${otherrowIndex}" name="per_watt[]" class="form-control text-end" value="0" readonly></td>
                                <td><a class="text-danger" href="javascript:otherRemoveItemRow(${otherrowIndex})"><i class="fas fa-trash"></i></a></td>
                                </tr>`;
                    otherrowCount++;
                    $('#otheritemsContainer').append(otherrowHTML);
                });
                /* /  Othres */

                const salerowIndex = 90001;
                const salerowHTML = `<tr class="sale-item-row" data-row-index="${salerowIndex}" data-row-special="" data-row-autoKw="1">
                                <td>1
                                    <input type="hidden" name="is_special[]" value="" />
                                    <input type="hidden" name="autoKw[]" value="" />
                                    <input type="hidden" name="category[]" value="sales" />
                                </td>
                                <td colspan="3"><input type="text" id="saleitem-${salerowIndex}" name="item[]" class="form-control" value="Sales Cost"></td>
                                <td><input type="number" id="saleqty-${salerowIndex}" name="qty[]" class="form-control" value="" oninput="calculateTotalSale(${salerowIndex},1)"></td>
                                <td><input type="number" id="salerate-${salerowIndex}" name="rate[]" class="form-control text-end" value="0" oninput="calculateTotalSale(${salerowIndex},1)"></td>
                                <td><input type="text" id="salesubtotal-${salerowIndex}" name="subtotal[]" class="form-control text-end" value="0" oninput="calculateTotalSale(${salerowIndex},2)"></td>
                                <td><input type="number" id="saletaxRate-${salerowIndex}" name="taxRate[]" class="form-control text-end" value="13.8" oninput="calculateTotalSale(${salerowIndex},1)"></td>
                                <td><input type="text" id="saletotalTax-${salerowIndex}" name="totalTax[]" class="form-control text-end" readonly></td>
                                <td><input type="text" id="saletotal-${salerowIndex}" name="total[]" class="form-control text-end" oninput="calculateTotalSale(${salerowIndex},3)"></td>
                                <td><input type="text" id="saleper-watt-${salerowIndex}" name="per_watt[]" class="form-control text-end" value="0" readonly></td>
                                <td></td>
                                </tr>`;
                $('#saleitemsContainer').append(salerowHTML);

                let cat = "";
                response.data.itemsOriginal.forEach(item => {
                    const rowIndex = rowCount;
                    let rowHTML = ``;
                    if (cat != "" && cat != item.category) {
                        rowHTML += ` <tr>
                                            <td colspan="12" class="text-end"><a href="javascript:void(0);" class="text-success add-new-btn" data-cat="${cat}" > <i class="fas fa-plus"> </i> Add New</a></td>
                                        </tr>`;
                    }
                    if (cat == "" || cat != item.category) {
                        rowHTML += `<tr style="--bs-table-accent-bg: #ff790061 !important;color: #000000;">
                                        <th colspan="12"><b>${item.category}</b></th>
                                    </tr>`;
                    }
                    cat = item.category;
                    let is_readonly = "readonly";
                    if (item.is_special == "panel") {
                        is_readonly = '';
                    }

                    rowHTML += `<tr class="item-row" data-row-index="${rowIndex}" data-row-special="${item.is_special}" data-row-autoKw="${item.is_auto_kw}">
                        <td>${rowIndex + 1}
                        <input type="hidden" name="is_special[]" value="${item.is_special}" />
                        <input type="hidden" name="autoKw[]" value="${item.is_auto_kw}" />
                        <input type="hidden" name="category[]" value="${item.category}" /></td>
                        <td><input type="text" id="item-${rowIndex}" name="item[]" class="form-control" value="${item.item}"></td>
                        <td><input type="text" id="unit-${rowIndex}" name="unit[]" class="form-control" value="${item.unit}"></td>
                        <td><input type="text" id="wpk-${rowIndex}" name="wpk[]" class="form-control" value="${item.wpk}"  ${is_readonly} oninput="calculateTotal(${rowIndex})"></td>
                        <td><input type="number" id="qty-${rowIndex}" name="qty[]" class="form-control" value="${item.qty}" oninput="calculateTotal(${rowIndex})"></td>
                        <td><input type="number" id="rate-${rowIndex}" name="rate[]" class="form-control text-end" value="${item.rate}" oninput="calculateTotal(${rowIndex})"></td>
                        <td><input type="text" id="subtotal-${rowIndex}" name="subtotal[]" class="form-control text-end" value="0" readonly></td>
                        <td><input type="number" id="taxRate-${rowIndex}" name="taxRate[]" class="form-control text-end" value="${item.taxRate}" oninput="calculateTotal(${rowIndex})"></td>
                        <td><input type="text" id="totalTax-${rowIndex}" name="totalTax[]" class="form-control text-end" readonly></td>
                        <td><input type="text" id="total-${rowIndex}" name="total[]" class="form-control text-end" readonly></td>
                        <td><input type="text" id="per-watt-${rowIndex}" name="per_watt[]" class="form-control text-end" value="0" readonly></td>
                        <td><a class="text-danger" href="javascript:removeItemRow(${rowIndex})"><i class="fas fa-trash"></i></a></td>
                        </tr>`;
                    rowCount++;
                    $('#itemsContainer').append(rowHTML);
                    calculateTotal(rowIndex);
                });
                $('#itemsContainer').append(`<tr><td colspan="12" class="text-end"><a href="javascript:void(0);" class="text-success add-new-btn" data-cat="${cat}"> <i class="fas fa-plus"> </i> Add New</a></td></tr>`);

                calculateTotalSale(90001);

                calculateTotalOther();

            }
        });
    });

    $('#mobile').on('input', function() {
        var mobileNumber = $(this).val();
        if (mobileNumber.length === 10 && /^\d+$/.test(mobileNumber)) {
            $.ajax({
                url: "{{route('search-lead')}}",
                method: 'GET',
                data: {
                    type: 'rateCalc',
                    mobileNumber: mobileNumber
                },
                success: function(response) {
                    $("#name").val(response);
                },
                error: function(xhr, status, error) {
                    // Handle errors

                    console.error(xhr.responseText);
                }
            });
        }
    });
</script>
@endsection