<style>
     ul.timelinev {
          list-style-type: none;
          position: relative;
     }

     ul.timelinev:before {
          content: ' ';
          background: #d4d9df;
          display: inline-block;
          position: absolute;
          left: 29px;
          width: 2px;
          height: 100%;
          z-index: 400;
     }

     ul.timelinev>li {
          margin: 20px 0;
          padding-left: 20px;
     }

     ul.timelinev>li:before {
          content: ' ';
          background: #22c0e8;
          display: inline-block;
          position: absolute;
          border-radius: 50%;
          border: 3px solid rgba(34, 192, 232, 0.47);
          left: 22px;
          width: 15px;
          height: 15px;
          z-index: 400;
     }

     ul.timelinev>li:last-child:before {
          background:rgb(151, 151, 151);
          border: 3px solid rgba(151, 151, 151, 0.47);
     }
</style>

<h5>
     <b> Item </b> : {{ getItemGropName($data->itemGroup) }} <br /><br />
     <b> Serial No. </b> : {{ $data->serial_number }}
</h5>

<ul class="timelinev mt-3 mb-5">
     <li>
          <a>{{ $data->purchase->grn_number }}</a>
          <a href="#" class="float-end">{{ date('d-m-Y',strtotime($data->purchase->date)) }} </a>
          <p><b>Supplier </b> : {{ $data->purchase->supplier->name }} <br /> <b>Invoice No.</b> : {{ $data->purchase->supplier_number }}</p>
     </li>

     @if(!is_null($data2))
     <li>
          <a>{{ $data2->delivery_challan->challan_number }}</a>
          <a href="#" class="float-end">{{ date('d-m-Y',strtotime($data2->delivery_challan->challan_date)) }} </a>
          <p><b>User </b> : {{ $data2->delivery_challan->salesQuatation->name }} <br /> <b>Mobile No.</b> : {{ $data2->delivery_challan->salesQuatation->mobile }}</p>
     </li>
     @endif
     @if(!is_null($data3))
     <li>
          <a>{{ $data3->salesMaster[0]->consumer_number ?? '' }}</a>
          <a href="#" class="float-end">{{ date('d-m-Y',strtotime($data3->created_at)) }} </a>
          <p><b>Consumer </b> : {{ $data3->salesMaster[0]->consumer_name ?? '' }} <br /> <b>Mobile No.</b> : {{ $data3->salesMaster[0]->contact_number ?? ''}}</p>
     </li>
     @endif
     @if(!is_null($data4))
     <li>
          <a>{{ $data4->salesMaster[0]->consumer_number ?? '' }}</a>
          <a href="#" class="float-end">{{ date('d-m-Y',strtotime($data4->created_at)) }} </a>
          <p><b>Consumer </b> : {{ $data4->salesMaster[0]->consumer_name ?? '' }} <br /> <b>Mobile No.</b> : {{ $data4->salesMaster[0]->contact_number ?? ''}}</p>
     </li>
     @endif

     <li>
          <a>&nbsp; </a>
     </li>

</ul>