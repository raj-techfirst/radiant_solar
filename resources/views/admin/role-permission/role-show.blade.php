@extends('layouts.app')
@section('title', 'Show Role')
@section('content')
<div class="row">
    <div class="col-12">
        <h4 class="content-header-title float-start">Show Role Permissions</h4>
        @can('role-list')
            <a href="{{route('roles.index')}}" role="button"  class="btn btn-sm btn-primary float-end"><i class="fa fa-angle-double-left me-25"></i> Role List</a>
        @endcan
    </div>
    <div class="col-12">
        <div class="card p-1">
            <div class="row">
                <div class="col-xs-12 col-sm-12 col-md-12">
                    <h4 class="fw-bolder">Role Name : <small class="badge badge-light-primary">{{ $role->name }}</small></h4>
                </div>
                <hr>
                <div class="col-xs-12 col-sm-12 col-md-12">
                    <h4 class="fw-bold">Permissions Assing :</h4>
                    @if(!empty($rolePermissions))
                        @foreach($rolePermissions as $key => $v)
                            @if($key % 2 == 0)
                                <label class="badge badge-light-info mb-1">{{$v->title_tag}} {{$v->title}}</label>
                            @else
                                <label class="badge badge-light-primary mb-1">{{$v->title_tag}} {{$v->title}}</label>
                            @endif
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
