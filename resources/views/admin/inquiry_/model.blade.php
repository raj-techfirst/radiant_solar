<div class="modal-header bg-transparent border-bottom">
    <h4 class="text-center mb-0" id="exampleModalTitle">Details :- <h6> ( Consumer Number : {{$inquiry->consumer_number}})</h6>
    </h4>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body px-2">
    <div class="row">
        <div class="col-12 col-md-6 col-lg-6">
            <ul class="p-0 m-0">
                <li class="d-flex pb-1 align-items-center">
                    <div class="avatar bg-light-success me-50 rounded p-1">
                        <i data-feather='user'></i>
                    </div>
                    <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                        <div class="me-2">
                            <small class=" d-block"><b>Consumer Name</b></small>
                            <h6 class="mb-0">{{$inquiry->consumer_name}}</h6>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
        <div class="col-12 col-md-6 col-lg-6">
            <ul class="p-0 m-0">
                <li class="d-flex pb-1 align-items-center">
                    <div class="avatar bg-light-info me-50 rounded p-1">
                        <i data-feather='phone'></i>
                    </div>
                    <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                        <div class="me-2">
                            <small class=" d-block">Conatct Number</small>
                            <h6 class="mb-0">{{$inquiry->contact_number}}</h6>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
        <div class="col-12 col-md-10 col-lg-10">
            <h5 class="mb-0">Problem :</h5>
            <p class="mb-1">{{$inquiry->problem}}</p>
        </div>
        <div class="col-2">
            @if(!is_null($inquiry->image))
            <a href="{{asset('upload/inquiry/' . $inquiry->image)}}" data-fancybox="gallery_ {{$inquiry->id}}" data-caption="{{$inquiry->title}}" class="gallary-item-overlay">
                <img class="img-fluid rounded" height="35" width="35" src="{{asset('upload/inquiry/' . $inquiry->image)}}" alt="{{$inquiry->title}}" title="{{$inquiry->title}}">
            </a>
            @endif
        </div>
    </div>

</div>