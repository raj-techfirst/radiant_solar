<tr data-repeater-item class="clone_row">
    <td class="text-center">
        <b class="sr_no">1</b>
    </td>
    <td class="custom-input-group">
        <div class="d-flex">
            <select class="form-select custom-select2 type" name="type" required>
                <option value="Item">BOS</option>
                <option value="ItemGroup">Panel/Inverter</option>
            </select>
        </div>
    </td>
    <td class="custom-input-group  type-item ">
        <div class="d-flex">
            <select class="form-select product_id custom-select2" name="item_id" required>
                <option value="" selected disabled>-- Select --</option>
                @foreach ($warehouseStock as $k => $v)
                <option value="{{ $v['id'] }}" data-unit="{{ $v['unit'] }}"  data-stock="{{ $v['stock'] }}">{{ $v['name'] }}</option>
                @endforeach
            </select>
        </div>
    </td>
    <td class="custom-input-group type-item-group d-none ">
        <div class="d-flex">

            <select class="form-select item_group_id custom-select2" name="item_group_id" required>
                <option value="" selected disabled>-- Select --</option>
                @foreach ($warehouseStockItemGroup as $k => $v)
                <option value="{{ $v['id'] }}" data-unit="{{ $v['unit'] }}" data-stock="{{ $v['stock'] }}">{{ $v['name'] }}</option>
                @endforeach
            </select>
        </div>
    </td>

    <td class="custom-input-group">
        <input type="hidden" class="stock-find" value="">
        <input type="number" class="form-control stock" placeholder="Quantity" value="" readonly>
    </td>
    <td class="custom-input-group">
        <div class="input-group">
            <input type="number" class="form-control quantity" name="quantity" required>
            <span class="input-group-text unit_type"></span>
        </div>
    </td>
    
    <td class="text-center">
        <button type="button" class="badge badge-light-danger border-0 remove-item">
            <i data-feather='trash-2'></i>
        </button>
    </td>
</tr>