<table class="table table-bordered table-sm">
    <thead>
        <tr>
            <th>Serial number</th>
        </tr>
    </thead>
    <tbody>
    @if(count($dupData))
        @foreach($dupData as $k => $v)
        <tr class="bg-light-danger">
            <td>{{$v[0]}}</td>
        </tr>
        @endforeach
        @endif
        @if(count($nData))
        @foreach($nData as $k => $v)
        <tr>
            <td>{{$v[0]}}</td>
        </tr>
        @endforeach
        @endif
        
    </tbody>
</table>