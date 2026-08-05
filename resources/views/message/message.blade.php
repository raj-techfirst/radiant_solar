@extends('layouts.app')
@section('title', 'Message')
@section('content')
<div class="row">
    <div class="col-12 mb-1">
        <h4 class="content-header-title float-start">View Message</h4>
    </div>
    <div class="col-12">
        <div class="card p-2">
            <table id="" class="datatables-basic table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Welcome</th>
                        <th>Follow Up</th>
                        <th>Not Interested</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                    </tr>
                    @foreach($message as $key => $item)
                    <tr>
                        <th>{{$key+1}}</th>
                        <th>{{$item->welcome}}</th>
                        <th>{{$item->follow_up}}</th>
                        <th>{{$item->not_interested}}</th>
                    </tr>
                    @endforeach

                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection