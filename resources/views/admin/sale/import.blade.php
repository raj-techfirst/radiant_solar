@extends('layouts.app')
@section('title', 'Sales Master')
@section('content')
<div class="row">
    <div class="col-12">
        @if((isset($salesMaster) && isset($salesMaster->id)))
        <h4 class="card-title mb-1">Edit Sales</h4>
        @else
        <h4 class="card-title mb-1">Add Sales</h4>
        @endif
    </div>
    <div class="col-12">
        <div class="card p-1">
            <form id="form"  class="form invoice-repeater" action="{{ route('sales-order-import') }}" method="POST" enctype="multipart/form-data">
                @csrf



                <div class="col-md-12 mt-2">
                    <input type="text" name="id" value="1" />
                    <input type="file" name="excel_file" />
                    <button type="submit" class="btn btn-sm btn-primary float-end save">{{ __('message.Submit') }}</button>
                </div>
        </div>
        </form>
    </div>
</div>
</div>
@endsection
@section('pagescript')
@endsection