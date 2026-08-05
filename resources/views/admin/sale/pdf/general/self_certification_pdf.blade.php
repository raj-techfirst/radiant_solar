<!DOCTYPE html>
<html>

<head>
    <style>
        html body {
            font-family: Roboto, 'Segoe UI', Tahoma, sans-serif;
            font-size: 83%;
            line-height: 1rem;
        }

        h4,
        h2,
        .center {
            text-align: center;
        }

        .left {
            text-align: left;
        }

        table {
            width: 100%;
            border-spacing: 0;
        }

        table,
        th,
        td {
            border: 1px solid #000;
        }

        .page-break {
            page-break-before: always;
        }

        .content {
            padding: 50px;
            padding-top: 120px;
            padding-bottom: 10px;
            background-image: url({{ public_path('img/pdf-bg.png') }});
            background-size: cover;
            background-repeat: no-repeat;
            background-position: center;
        }

        .first-page {
            background-image: none !important;
            padding: 10px 50px 0 50px;
        }

        @page {
            margin: 0px !important;
        }

        p {
            text-align: justify;
        }
    </style>
</head>

<body>
    <div class="first-page">
        <table cellpadding="3" style="border-bottom: none;">
            <tr>
                <td class="center" colspan="4"><b><u>Self-Certification for Solar Roof top Installations up to 10KW
                            -NP</u></b></td>
            </tr>
            <tr>
                <td colspan="4">This is to certify that the installation of Solar roof top power plant along with its
                    associated equipment of capacity @if ($installation_data)
                        ({{ $installation_data->penal_nos }}) X ({{ $installation_data->panelwatt->name }} Wp) =
                        {{ number_format($installation_data->total_kv, 3, '.', '') }} KW
                    @else
                        0
                    @endif
                    KW total capacity at: <b>{{ $order_data->consumer_name }}</b> has been carried out by us/me and the
                    details of the Installation as well as the test results are as under :</td>
            </tr>
            <tr>
                <td colspan="4"><b>1. Details of Consumer</b></td>
            </tr>
            <tr>
                <td colspan="2" style="width: 250px;">Name.</td>
                <td style="width: 250px"><b>{{ $order_data->consumer_name }}</b></td>
                <td rowspan="4" style="width: 250px;">{{ $order_data->address }}</td>
            </tr>
            <tr>
                <td colspan="2">Electricity Connection no.</td>
                <td>{{ $order_data->consumer_number }}</td>
            </tr>
            <tr>
                <td colspan="2">Project registration no.</td>
                <td>{{ $order_data->ragistration_number }}</td>
            </tr>
            <tr>
                <td colspan="2">Applied PV capacity:</td>
                <td><b>
                        @if ($installation_data)
                            {{ number_format($installation_data->total_kv, 3, '.', '') }}
                        @else
                            0
                        @endif
                    </b> KW</td>
            </tr>
            <tr>
                <td colspan="5"><b>2. Details Of Solar PV cells And Inverter</b></td>
            </tr>
        </table>
        <table cellpadding="3" style="border-top: none; border-bottom: none;">
            <tr>
                <td style="width: 10%;" class="center"><b>No.</b></td>
                <td style="width: 30%;" class="center"><b>Particular</b></td>
                <td style="width: 30%;" class="center">Solar PV Cell (Modules)</td>
                <td style="width: 30%;" class="center">INVERTER</td>
            </tr>
            @php
                $company_name = $invater_kw = $serial_no = $model_no = '';
                $total_invater_kw = $invaterVoltage = 0;
            @endphp
            @foreach ($installation_invater_data as $ri)
                @php
                    $company_name != '' && ($company_name .= ',');
                    $company_name .= $ri->name;

                    $invaterVoltage += $ri->voltage;

                    $serial_no != '' && ($serial_no .= ',');
                    $serial_no .= $ri->serial_no_of_inverter;

                    $invater_kw != '' && ($invater_kw .= ',');
                    $invater_kw .= $ri->invater_kw;

                    $model_no != '' && ($model_no .= ',');
                    $model_no .= $ri->model_number;

                    $total_invater_kw += $ri->invater_kw;
                @endphp
            @endforeach

            <tr>
                <td class="center">1.</td>
                <td class="center">Make</td>
                <td class="center"><b>{{ $installation_data->panelcompany->name ?? '' }}</b></td>
                <td class="center"><b>{{ $company_name }}</b></td>
            </tr>
            <tr>
                <td class="center">2.</td>
                <td class="center">Capacity</td>
                <td class="center"><b>{{ $installation_data->panelwatt->name ?? '' }}</b> W</td>
                <td class="center"><b>{{ $invater_kw }}</b> KW</td>
            </tr>
            <tr>
                <td class="center">3.</td>
                <td class="center">No. of Module/invertor</td>
                <td class="center"><b>{{ $installation_data->penal_nos ?? '' }}</b></td>
                <td class="center"><b>{{ $installation_data->no_of_inverter ?? '' }}</b></td>
            </tr>
            <tr>
                <td class="center">4.</td>
                <td class="center">Total capacity</td>
                <td class="center">
                    <b>{{ isset($installation_data->total_kv) ? number_format($installation_data->total_kv, 3, '.', '') : '' }}</b>
                    KW</td>
                <td class="center"><b>{{ $total_invater_kw }}</b> KW</td>
            </tr>
            <tr>
                <td class="center">5.</td>
                <td class="center">Voltage</td>
                <td class="center"><b></b></td>
                <td class="center"><b>{{ $invaterVoltage }} V</b></td>
            </tr>
            <tr>
                <td class="center">6.</td>
                <td class="center">Voltage</td>
                <td class="center" colspan="2"><b>Attached separeted sheet</b></td>
            </tr>
            <tr>
                <td colspan="4">* Inverter rated /nominal capacity is mentioned.<br>* Inverter and module data sheet
                    attached .</td>
            </tr>
        </table>
        <table cellpadding="3" style="border-top: none; border-bottom: none;">
            <tr>
                <td colspan="4"><b>3. Test Result</b></td>
            </tr>
            <tr>
                <td class="center" colspan="2"><b>Earthing</b></td>
                <td class="center" colspan="2"><b>Insulation resistance</b></td>
            </tr>
            <tr>
                <td colspan="2"><b>Earth Tester Sr no.-382194</b></td>
                <td colspan="2"><b>Meggar Sr no and voltage: 18090109</b></td>
            </tr>
            <tr>
                <td colspan="2">Earth Resistance values for all Earth Pits-<br>1.
                    {{ $installation_data->dc_side ?? '' }} <span
                        style="font-family: DejaVu Sans, sans-serif;">&#937;</span> <span style="text-align: right;">
                        <span style="padding-left: 100px;"> 3. {{ $installation_data->ac_side ?? '' }} </span><span
                            style="font-family: DejaVu Sans, sans-serif;">&#937;</span> </span><br>2.
                    {{ $installation_data->la_earthing ?? '' }}<span
                        style="font-family: DejaVu Sans, sans-serif;">&#937;</span> </td>
                <td colspan="2">Insulation Resistance :<br><span style="padding-left: 50px;">1. Phase to Phase :
                        {{ $installation_data->phase_to_phase ?? '' }}M<span
                            style="font-family: DejaVu Sans, sans-serif;">&#937;</span></span> <br><span
                        style="padding-left: 50px;">2. Phase to Earth :
                        {{ $installation_data->phase_to_earth ?? '' }}M<span
                            style="font-family: DejaVu Sans, sans-serif;">&#937;</span> </span></td>
            </tr>
            <tr>
                <td colspan="4" style="padding-left: 30px;">
                    The work of aforesaid installation has been completed by us on Date
                    <b>{{ $installation_data && $installation_data->date ? date('d-m-Y', strtotime($installation_data->date)) : '' }}</b>
                    and it is to hereby declare that
                </td>
            </tr>
            <tr>
                <td colspan="4">a) All PV modules and its supporting structures have enough mechanical strength and
                    it conforms to the relevant codes/guidelines for registration of residential project under rooftop
                    solar subsidy programme in National Portal for Rooftop Solar & subsequent amendments.</td>
            </tr>
            <tr>
                <td colspan="4">b) All cables/wires, Modules, inverters, protective switchgears as well as Earthlings
                    are of adequate ratings/size and they conforms to the requirements of Central Electricity Authority
                    (Measures relating to safety and electrical supply), Regulations 2010 & subsequent amendments as
                    well as the relevant codes/guidelines for registration of residential project under rooftop solar
                    subsidy programme in National Portal for Rooftop Solar & subsequent amendments.</td>
            </tr>
            <tr>
                <td colspan="4">c) The work of aforesaid Installation has been carried out in conformance with the
                    requirements of CentralElectricity Authority (Measures relating to safety and electrical supply),
                    Regulations 2010 & subsequent amendments and the relevant codes/guidelines for registration of
                    residential project under rooftop solar subsidy programme in National Portal for Rooftop Solar &
                    subsequent amendments. The installation is tested by us and is found safe to be energized.</td>
            </tr>
        </table>
        <table cellpadding="3" style="border-top: none; border-bottom: none;">
            <tr>
                <td style="width: 15%;">Date</td>
                <td style="width: 15%;">
                    {{ $installation_data && $installation_data->date ? date('d-m-Y', strtotime($installation_data->date)) : '' }}
                </td>
                <td style="width: 70%;"></td>
                <!-- <td colspan="2"></td> -->
            </tr>
        </table>
        <table cellpadding="3" style="border-top: none;">
            <tr>
                <td style="padding: 00px 0 0 5px;" colspan="2">
                    @if ($type == '1')
                        @php
                            $imagePath = public_path('img/supervisor.png'); // Adjust the path as needed
                            $imageData = base64_encode(file_get_contents($imagePath));
                            $imageMime = mime_content_type($imagePath);
                            $imageSrc = 'data:' . $imageMime . ';base64,' . $imageData;
                        @endphp
                        <center><img src="{{ $imageSrc }}" style="max-height:130px" /></center>
                    @else
                        <br /><br /><br /><br />
                    @endif

                    Signature of Electrical Supervisor<br>Name <!-- of Electrical Supervisor -->
                    -<b>{{ env('APP_ELECTRICAL_SUPERVISOR') }}</b><br>Permit No.-
                    <b>{{ env('APP_ELECTRICAL_SUPERVISOR_PERMIT_NO') }}</b>
                </td>
                <td style="padding: 0px 0 0 5px;" colspan="2">
                    @if ($type == '1')
                        @php
                            $contractorimagePath = public_path('img/contractor.png'); // Adjust the path as needed
                            $contractorimageData = base64_encode(file_get_contents($contractorimagePath));
                            $contractorimageMime = mime_content_type($contractorimagePath);
                            $contractorimageSrc = 'data:' . $contractorimageMime . ';base64,' . $contractorimageData;
                        @endphp
                        <center><img src="{{ $contractorimageSrc }}" style="max-height:130px" /></center>
                    @else
                        <br /><br /><br /><br />
                    @endif
                    Signature of Licensed Electrical Contractor<br>Name <!-- of Licensed Electrical Contractor --> -
                    <b>{{ env('APP_ELECTRICAL_CONTRACTOR') }}</b><br>Electrical Contractor License No.-
                    <b>{{ env('APP_ELECTRICAL_LICENSE_NO') }}</b><br>Valid up to.-
                    <b>{{ env('APP_ELECTRICAL_LICENSE_VALID_UP_TO') }}</b>
                </td>
            </tr>
        </table>
    </div>
    <div class="content">
        <p>Consumer No. <b>{{ $order_data->consumer_number }}</b></p>
        <p>Customer Name: <b>{{ $order_data->consumer_name }}</b></p>
        <p>Project registration no. <b>{{ $order_data->ragistration_number }}</b></p>
        <p>PV Module Specification:</p>
        <table>
            <tr>
                <td style="width: 25%;" class="center"><b>Equipment</b></td>
                <td class="center" style="width: 25%;"><b>Make of Solar PV Module</b></td>
                <td class="center" style="width: 25%;"><b>Model No.</b></td>
                <td class="center" style="width: 25%;"><b>Type of PV modules(Crystalline)</b></td>
            </tr>
            <tr>
                <td rowspan="3" class="center">PV Module:</td>
                <td class="center">{{ $installation_data->panelcompany->name ?? '' }}</td>
                <td class="center">{{ $installation_data->penal_model_no ?? '' }}</td>
                <td class="center">{{ $installation_data->paneltype->name ?? '' }}</td>
            </tr>
            <tr>
                <td><b>Rated Capacity of Solar Module in Watt</b></td>
                <td class="center"><b>No. of Modules</b></td>
                <td class="center"><b>Total PV Capacity installed in Kwp</b></td>
            </tr>
            <tr>
                <td class="center"><b>{{ $installation_data->panelwatt->name ?? '' }}</b></td>
                <td class="center"><b>{{ $installation_data->penal_nos ?? '' }}</b></td>
                <td class="center">
                    <b>{{ isset($installation_data->total_kv) ? number_format($installation_data->total_kv, 3, '.', '') : '' }}</b>
                </td>
            </tr>
        </table><br>
        <table>
            <tr>
                <td colspan="4" style="text-align: center;"><b>Serial No. of PV Module</b></td>
            </tr>
            @php
                $count = 0;
            @endphp
            @foreach ($installation_panel_data as $val)
                @if ($count % 4 == 0)
                    <tr>
                @endif
                <td>{{ $val->serial_no }}</td>
                @php
                    $count++;
                @endphp
                @if ($count % 4 == 0)
                    </tr>
                @endif
            @endforeach
            @if ($count % 4 != 0)
                </tr>
            @endif
        </table>
        <p>Inverter Details:</p>
        <table>
            <tr>
                <td class="center" style="width: 25%;"><b>Make of Inverter</b></td>
                <td class="center" style="width: 25%;"><b>Model No.</b></td>
                <td class="center" style="width: 25%;"><b>Rated A.C. Output of Inverter in kilo Watt</b></td>
                <td class="center" style="width: 25%;"><b>Serial No. of Inverter</b></td>
            </tr>
            @foreach ($installation_invater_data as $val)
                <tr>
                    <td class="center"><b>{{ $val->name }}</b></td>
                    <td class="center"><b>{{ $val->model_number }}</b></td>
                    <td class="center"><b>{{ $val->invater_kw }} KW</b></td>
                    <td class="center"><b>{{ $val->serial_no_of_inverter }}</b></td>
                </tr>
            @endforeach
        </table>
    </div>
    @if ($type == '1')
        @php
            $imagePath = public_path('img/stamp.png');
            $imageData = base64_encode(file_get_contents($imagePath));
            $imageMime = mime_content_type($imagePath);
            $imagestamp = 'data:' . $imageMime . ';base64,' . $imageData;
        @endphp


        <p style="text-align: right;"> <img src="{{ $imagestamp }}" style="margin-right:50px;" /> </p>
    @endif
</body>

</html>
