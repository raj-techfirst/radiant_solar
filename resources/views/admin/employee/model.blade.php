<!--Start Model Card Open-->
<div class="row">
    <div class="col-12">
        <div class="card mb-0">
            <div class="table-responsive">
                <table class="table">
                    <thead class="table-dark">
                        <tr>
                            <th>{{ __('message.Name') }}</th>
                            <th>{{ __('message.State') }}</th>
                            <th>{{ __('message.City') }}</th>
                            <th>{{ __('message.Address') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>{{$companyProfile->user->name}} {{$companyProfile->user->last_name}}</td>
                            <td>{{$companyProfile->state->state_name}}</td>
                            <td>{{$companyProfile->city->city_name}}</td>                            
                             <td>{{$companyProfile->address}}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<!--End Model Card Open-->