<table class="table table-bordered table-sm">
    <thead>
        <tr>
            <th>Action</th>
            <th>Serial number</th>
            <th>Warranty start</th>
            <th>Warranty end</th>
            <th>Guarantee start</th>
            <th>Guarantee end</th>
        </tr>
    </thead>
    <tbody>
        @if(count($serialNumber))
        @foreach($serialNumber as $k => $v)
        <tr>
            <td class="text-center">
                <a  data-id="{{ $v->id }}"
                    data-warranty-start='{{ ($v->warranty_start_date != "0000-00-00") ? $v->warranty_start_date : "" }}'
                    data-warrantyend='{{ ($v->warranty_end_date != "0000-00-00") ? $v->warranty_end_date : "" }}'
                    data-guarantee-start='{{ ($v->guarantee_start_date != "0000-00-00") ? $v->guarantee_start_date : "" }}'
                    data-guarantee-end='{{ ($v->guarantee_start_date != "0000-00-00") ? $v->guarantee_start_date : "" }}'
                    data-serialno="{{$v->serial_number}}" href="javascript:void(0);" class="avatar bg-light-info p-50 m-0 edit-serial-number data_serial_number_{{ $v->id }}" data-bs-toggle="tooltip" data-placement="left" title="" data-bs-original-title="Edit" aria-label="Edit"><i class="fa fa-edit"></i></a>
            </td>
            <td class="view_serial_number_{{ $v->id }}">{{$v->serial_number}}</td>
            <td class="view_warranty_start_date_{{ $v->id }}">{{ ($v->warranty_start_date != "0000-00-00") ? date('d-m-Y',strtotime($v->warranty_start_date)) : '' }}</td>
            <td class="view_warranty_end_date_{{ $v->id }}">{{ ($v->warranty_end_date != "0000-00-00") ? date('d-m-Y',strtotime($v->warranty_end_date)) : '' }}</td>
            <td class="view_guarantee_start_date_{{ $v->id }}">{{ ($v->guarantee_start_date != "0000-00-00") ? date('d-m-Y',strtotime($v->guarantee_start_date)) : '' }}</td>
            <td class="view_guarantee_end_date_{{ $v->id }}">{{ ($v->guarantee_end_date != "0000-00-00") ? date('d-m-Y',strtotime($v->guarantee_end_date)) : '' }}</td>
        </tr>
        @endforeach
        @endif

    </tbody>
</table>