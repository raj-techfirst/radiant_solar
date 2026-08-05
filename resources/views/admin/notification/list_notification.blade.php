@if($getNotification->count() > 0)
@if($getCountUnReadNotification > 0)
@php
$allCount = $getCountUnReadNotification;
$clickClass = 'read-all';
@endphp
@else
@php
$allCount = '';
$clickClass = '';
@endphp
@endif
<a class="nav-link {{$clickClass}} dropdown-toggle dropdown-toggle-split" id="navbarDropdown" data-bs-toggle="dropdown" href="javascript:void(0);" aria-haspopup="true" aria-expanded="false">
     <img src="{{asset('img/notification.svg')}}">
     @if($allCount > 0)
     <span class="notify badge badge-pill bg-danger badge-up totalCount">{{$allCount}}</span>
     @endif
</a>

<div class="dropdown-menu notification-menu m-0 dropdown-menu-end" aria-labelledby="navbarDropdown">
     <span class="dropdown-item dropdown-header">{{$getNotification->count()}} Notifications</span>
     @foreach($getNotification as $key => $gn)
     @if($key == 0) <span class="notify_id" data-id="{{$gn->user_id}}" data-count="{{$gn->count}}"></span> @endif
     <div class="dropdown-divider @if($key == 0) mt-0 @endif"></div>
     <a class="dropdown-item" href="">
          <span class="text-muted text-sm me-4">{{$gn->title}}</span>
          @if($gn->lead_type == 0)
          <span class="text-muted text-sm">{{$gn->created_at->format('d M Y')}}</span>
          @else
          @php
          $reminder_date ='';
          if($gn->reminder_date != null){
               $reminder_date = date("d-m-Y", strtotime($gn->reminder_date));
          }
          @endphp
          <span class="text-muted text-sm">{{$reminder_date}}</span>
          @endif
          <p class="mb-0">{{$gn->description}}</p>
     </a>
     @endforeach
</div>
@else
<a class="nav-link dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" href="javascript:void(0);">
     <!-- <i class="fa fa-bell"></i> -->
     <img src="{{asset('img/notification.svg')}}">
</a>
<div class="dropdown-menu w-100 m-0 dropdown-menu-start" style="max-height: 300px; overflow: auto;">
     <span class="dropdown-item dropdown-header">0 {{ __('message.Notifications') }}</span>
</div>
@endif