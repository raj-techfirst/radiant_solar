<table class="table table-bordered table-sm">
    <thead>
        <tr>
            <th>Serial number</th>
            <th>Warranty start</th>
            <th>Warranty end</th>
            <th>Guarantee start</th>
            <th>Guarantee end</th>
        </tr>
    </thead>
    <tbody>
    @if(count($dupData))
        @foreach($dupData as $k => $v)
        <tr class="bg-light-danger">
            <td>{{$v[0]}}</td>
            <td>{{$v[1]}}</td>
            <td>{{$v[2]}}</td>
            <td>{{$v[3]}}</td>
            <td>{{$v[4]}}</td>
        </tr>
        @endforeach
        @endif
        @if(count($nData))
        @foreach($nData as $k => $v)
        <tr>
            <td>{{$v[0]}}</td>
            <td>{{$v[1]}}</td>
            <td>{{$v[2]}}</td>
            <td>{{$v[3]}}</td>
            <td>{{$v[4]}}</td>
        </tr>
        @endforeach
        @endif
        
    </tbody>
</table>