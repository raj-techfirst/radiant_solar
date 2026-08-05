@php $no = 1; @endphp
@foreach($warehouseStock as $key => $item)
<tr class="clone_row">
    <td class="text-center">
        {{ $key+1 }}
    <input type="hidden" name="id[]" value="{{$item->id}}">

    </td>
    @if($item->type == "Item")
    <td>
        <input type="hidden" name="item_id[]" value="{{$item->item->id}}">
        <input type="hidden" name="item_group_id[]" value="0">

        {{$item->item->name}}
    </td>
    @else   
    <td>
        <input type="hidden" name="item_group_id[]" value="{{$item->itemGroup->id}}">
        <input type="hidden" name="item_id[]" value="0">
        {{ getItemGropName($item,1) }}
    </td>
    @endif
    <td class="text-center" style="width: 200px;"><span class="current_stock">{{$item->quantity}}</span></td>
    <td class="text-center" style="width: 200px;"><input type="number" autocomplete="off" class="form-control real_stock" name="real_stock[]" value="" placeholder="Real Stock"></td>
    <td class="text-center" style="width: 200px;"><input type="number" autocomplete="off" class="form-control difference" name="difference[]" value="" placeholder="Difference" readonly></td>
</tr>
@endforeach