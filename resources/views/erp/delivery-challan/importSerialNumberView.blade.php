<table class="table table-bordered table-sm">
    <thead>
        <tr>
            <th>Serial number</th>
        </tr>
    </thead>
    <tbody>
        @if(count($serialNumber))
        @foreach($serialNumber as $k => $v)
        <tr>
            <td class="view_serial_number_{{ $v->id }}">{{$v->serialNumbers->serial_number}}</td>
        </tr>
        @endforeach
        @endif

    </tbody>
</table>