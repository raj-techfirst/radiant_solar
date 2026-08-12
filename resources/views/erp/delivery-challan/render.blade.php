@if(count($deliveryChallanMeta) > 0)
@foreach($deliveryChallanMeta as $key => $item)
<tr data-repeater-item class="clone_row">
    <td class="text-center">
        <b class="sr_no">{{$key+1}}</b>
    </td>
    <td class="custom-input-group">
        <div class="d-flex">
            <select class="form-select custom-select2 type" name="type" required>
                <option value="Item" {{ $item->type == "Item" ? 'selected' : '' }}>BOS</option>
                <option value="ItemGroup" {{ $item->type == "ItemGroup" ? 'selected' : '' }}>Panel/Inverter</option>
            </select>
        </div>
    </td>
    <td class="custom-input-group  type-item {{ $item->type != 'Item' ? 'd-none' : '' }} ">
        <div class="d-flex">
            <input type="hidden" name="delivery_challan_meta_id" value="{{$item->id}}">
            <select class="form-select product_id custom-select2" name="item_id" required>
                <option value="" selected disabled>-- Select --</option>
                @foreach ($warehouseStock as $k => $v)
                <option value="{{ $v['id'] }}" data-price="{{ $v['price'] }}" data-unit="{{ $v['unit'] }}" data-gst="{{ $v['gst_rate'] }}" data-stock="{{ $v['stock'] + (($item->item_id == $v['id']) ? $item->quantity : 0) }}" {{ ($item->item_id == $v['id'] ) ? 'selected' : '' }}>{{ $v['name'] }}</option>
                @endforeach
            </select>
        </div>
    </td>
    <td class="custom-input-group type-item-group {{ $item->type != 'ItemGroup' ? 'd-none' : '' }} ">
        <div class="d-flex">
            <select class="form-select item_group_id custom-select2" name="item_group_id" required>
                <option value="" selected disabled>-- Select --</option>
                @foreach ($warehouseStockItemGroup as $k => $v)
                <option value="{{ $v['id'] }}"  data-price="{{ $v['price'] }}" data-unit="{{ $v['unit'] }}" data-gst="{{ $v['gst_rate'] }}" data-stock="{{ $v['stock'] + (($item->item_group_id == $v['id']) ? $item->quantity : 0) }}" {{ ($item->item_group_id == $v['id'] ) ? 'selected' : '' }}>{{ $v['name'] }}</option>
                @endforeach
            </select>
        </div>
    </td>
    <td class="custom-input-group"></td>
    <td class="custom-input-group">
        <input type="hidden" class="stock-find" value="">
        <input type="number" class="form-control stock" placeholder="Quantity" value="" readonly>
    </td>
    <td class="custom-input-group">
        <div class="input-group">
            <input type="number" class="form-control quantity" name="quantity" value="{{$item->quantity}}" required>
            <span class="input-group-text unit_type"></span>
        </div>
    </td>
    <td class="custom-input-group d-none">
        <input type="text" class="form-control gst" value="" readonly>
    </td>
    <td class="custom-input-group d-none">
        <input type="number" min="1" class="form-control rate number" name="rate" placeholder="Rate" value="{{$item->rate}}" required>
    </td>
    <td class="custom-input-group d-none">
        <input type="number" class="form-control gst-amt number" name="gst_amt" value="" readonly>
    </td>
    <td class="custom-input-group d-none">
        <input type="number" class="form-control amount number" name="amount" placeholder="Amount" value="" readonly>
    </td>
    <td class="text-center">
        <button type="button" class="badge badge-light-danger border-0 variant-delete m-0" data-id="{{$item->id}}">
            <i data-feather='trash-2'></i>
        </button>
    </td>
</tr>
@endforeach
@elseif(count($bomData) > 0)

@foreach($bomData as $key => $item)

<tr data-repeater-item class="clone_row">
    <td class="text-center">
        <b class="sr_no">{{$key+1}}</b>
    </td>
    <td class="custom-input-group">
        <div class="d-flex">
            <select class="form-select custom-select2 type" name="type" required>
                <option value="Item" {{ $item->type == "Item" ? 'selected' : '' }}>BOS</option>
                <option value="ItemGroup" {{ $item->type == "ItemGroup" ? 'selected' : '' }}>Panel/Inverter</option>
            </select>
        </div>
    </td>
    <td class="custom-input-group  type-item {{ $item->type != 'Item' ? 'd-none' : '' }} ">
        <select class="form-select product_id custom-select2" name="item_id" required>
            <option value="" selected disabled>-- Select --</option>
            @php $itemName = false; @endphp
            @foreach ($warehouseStock as $k => $v)

            @if($itemName == false && $item->item_id == $v['id'])
            @php $itemName = true; @endphp
            @endif

            <option value="{{ $v['id'] }}"  data-price="{{ $v['price'] }}" data-unit="{{ $v['unit'] }}" data-gst="{{ $v['gst_rate'] }}" data-stock="{{ $v['stock'] }}" {{ ($item->item_id == $v['id'] ) ? 'selected' : '' }}>{{ $v['name'] }}</option>
            @endforeach
        </select>

        @if($item->type == 'Item' && $itemName == false)
        <small class="w-100 text-danger no-stock">{{ $item->product->name }} ({{ $item->quantity }})</small>
        @endif
    </td>
    <td class="custom-input-group type-item-group {{ $item->type != 'ItemGroup' ? 'd-none' : '' }} ">
        <select class="form-select item_group_id custom-select2" name="item_group_id" required>
            <option value="" selected disabled>-- Select --</option>
            @php $itemGroupName = false; @endphp
            @foreach ($warehouseStockItemGroup as $k => $v)

            @if($itemGroupName == false && $item->item_group_id == $v['id'])
            @php $itemGroupName = true; @endphp
            @endif

            <option value="{{ $v['id'] }}"  data-price="{{ $v['price'] }}" data-unit="{{ $v['unit'] }}" data-gst="{{ $v['gst_rate'] }}" data-stock="{{ $v['stock'] }}" {{ ($item->item_group_id == $v['id']) ? 'selected' : '' }}>{{ $v['name'] }}</option>
            @endforeach
        </select>

        @if($item->type == 'ItemGroup' && $itemGroupName == false)
        <small class="w-100 text-danger no-stock">
            {{ getItemGropName($item,1) }} ({{ $item->quantity }})</small>
        @endif
    </td>
    <td class="custom-input-group text-center required-item">
      {{$item->quantity}} <span class="unit_type"></span>
    </td>
    <td class="custom-input-group">
        <input type="hidden" class="stock-find" value="">
        <input type="number" class="form-control stock" placeholder="Quantity" value="" readonly>
    </td>
    <td class="custom-input-group">
        <div class="input-group">
            <input type="number" class="form-control quantity" name="quantity" value="{{$item->quantity}}" required>
            <span class="input-group-text unit_type"></span>
        </div>
    </td>
    <td class="custom-input-group @if($quotationsId == 0) d-none @endif">
        <input type="text" class="form-control gst" value="" readonly>
    </td>
    <td class="custom-input-group @if($quotationsId == 0) d-none @endif">
        <input type="number" min="1" class="form-control rate number" name="rate" placeholder="Rate" value="" required>
    </td>
    <td class="custom-input-group @if($quotationsId == 0) d-none @endif">
        <input type="number" class="form-control gst-amt number" name="gst_amt" value="" readonly>
    </td>
    <td class="custom-input-group @if($quotationsId == 0) d-none @endif">
        <input type="number" class="form-control amount number" name="amount" placeholder="Amount" value="" readonly>
    </td>
    <td class="text-center">
        <button type="button" class="badge badge-light-danger border-0 remove-item m-0">
            <i data-feather='trash-2'></i>
        </button>
    </td>
</tr>

@endforeach


@else
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
                <option value="{{ $v['id'] }}" data-price="{{ $v['price'] }}"  data-unit="{{ $v['unit'] }}" data-gst="{{ $v['gst_rate'] }}" data-stock="{{ $v['stock'] }}">{{ $v['name'] }}</option>
                @endforeach
            </select>
        </div>
    </td>
    <td class="custom-input-group type-item-group d-none ">
        <div class="d-flex">

            <select class="form-select item_group_id custom-select2" name="item_group_id" required>
                <option value="" selected disabled>-- Select --</option>
                @foreach ($warehouseStockItemGroup as $k => $v)
                <option value="{{ $v['id'] }}" data-price="{{ $v['price'] }}" data-unit="{{ $v['unit'] }}" data-gst="{{ $v['gst_rate'] }}" data-stock="{{ $v['stock'] }}">{{ $v['name'] }}</option>
                @endforeach
            </select>
        </div>
    </td>

    <td class="custom-input-group"></td>
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
    <td class="custom-input-group @if($quotationsId == 0) d-none @endif">
        <input type="text" class="form-control gst" value="" readonly>
    </td>
    <td class="custom-input-group @if($quotationsId == 0) d-none @endif">
        <input type="number" min="1" class="form-control rate number" name="rate" placeholder="Rate" value="" required>
    </td>
    <td class="custom-input-group @if($quotationsId == 0) d-none @endif">
        <input type="number" class="form-control gst-amt number" name="gst_amt" value="" readonly>
    </td>
    <td class="custom-input-group @if($quotationsId == 0) d-none @endif">
        <input type="number" class="form-control amount number" name="amount" placeholder="Amount" value="" readonly>
    </td>
    <td class="text-center">
        <button type="button" class="badge badge-light-danger border-0 remove-item m-0">
            <i data-feather='trash-2'></i>
        </button>
    </td>
</tr>
@endif