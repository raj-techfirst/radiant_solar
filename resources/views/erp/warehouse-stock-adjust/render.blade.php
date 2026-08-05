@php $no = 1; @endphp
@foreach($warehouseStock as $key => $item)
<tr class="clone_row">
    <td class="text-center">{{ $key+1 }}</td>
    <td>
        @php
        $display_name = $product_id = $item_group_id = '0';
        if ($item->type == "Item") {
        $display_name = $item->item->name;
        $product_id = $item->item->id;
        } else {
        $item_group_id = $item->itemGroup->id;
        $display_name = getItemGropName($item,1);
        }
        @endphp
        <input type="hidden" name="id[]" value="{{$item->id}}">
        <input type="hidden" name="item_id[]" value="{{$product_id}}">
        <input type="hidden" name="item_group_id[]" value="{{$item_group_id}}">
        <input type="hidden" name="item_type[]" value="{{$item->type}}">
        {{ $display_name}}
    </td>
    <td class="text-center" style="width: 200px;"><span class="current_stock">{{$item->quantity}}</span></td>
    <td class="text-center" style="width: 200px;"><input type="number" autocomplete="off" class="form-control real_stock" name="real_stock[]" value="" placeholder="Real Stock"></td>
    <td class="text-center" style="width: 200px;"><input type="number" autocomplete="off" class="form-control difference" name="difference[]" value="" placeholder="Difference" readonly></td>
</tr>
@endforeach