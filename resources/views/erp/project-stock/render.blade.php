@php $no = 1; @endphp
@foreach($warehouseStock as $key => $value)
@if($value->quantity != 0)
<tr>
    <td class="text-center">
        <b class="sr_no">{{ $no++ }}</b>
        <input type="hidden" name="id[]" value="{{ $value->id }}">
    </td>

    @if($value->type == "Item")

    <td class="custom-input-group">
        <input type="hidden" name="item_id[]" value="{{ $value->item_id }}">
        <input type="text" class="form-control product" placeholder="Product" value="{{ $value->item->name }}" readonly>
    </td>

    @else

    <td class="custom-input-group">
        <input type="hidden" name="item_group_id[]" value="{{ $value->item_group_id }}">
        <input type="text" class="form-control product" placeholder="Product" value="{{ getItemGropName($value) }}" readonly>
    </td>

    @endif

    <td class="custom-input-group">
        <input type="hidden" class="stock-find" value="{{ $value->quantity }}">
        <input type="number" class="form-control stock" placeholder="Quantity" value="{{ $value->quantity }}" readonly>
    </td>
    <td class="custom-input-group">
        <input type="number" min="1" max="{{ $value->quantity }}" class="form-control quantity number" name="quantity[]" placeholder="Transfer Qty." value="" required>
    </td>
    <td class="custom-input-group text-center">
        <button type="button" class="badge badge-light-danger border-0 remove-item">
            <i data-feather='trash-2'></i>
        </button>
    </td>
</tr>
@endif
@endforeach