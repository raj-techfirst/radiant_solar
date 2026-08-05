<style>
    .payment-table>:not(caption)>*>* {
        padding: 0.1rem 0.3rem;
        font-size: 12px;
    }
</style>
<div class="col-md-12">
    <hr />
</div>
<div class="col-md-12">
    <div class="table-responsive">
        <table id="table" class="datatables-basic table table-hover payment-table">
            <thead>
                <tr>
                    <th>Status</th>
                    <th>Amount</th>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Cheque/UTR/UPI</th>
                    <th>Bank Name</th>
                </tr>
            </thead>
            <tbody>
                @if($payment->count() > 0)
                @foreach($payment as $key => $value)
                <tr>
                    <td>
                        @php $payStatus = getPaymentStatus($value->status); @endphp
                        <span class="badge bg-light-{{ $payStatus['class'] }} w-100">{{ $payStatus['status'] }}</span>
                    </td>
                    <td>{{$value->amount}}</td>
                    <td>{{ date('d-m-Y',strtotime($value->payment_date))}}</td>
                    <td>{{$value->payment_type}}</td>
                    <td>{{$value->cheque_number}} {{$value->utr_number}} {{$value->upi_id}}</td>
                    <td>{{$value->bank_name}}</td>
                </tr>
                @endforeach
                @else
                <tr>
                    <td colspan="6">No Data!</td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>