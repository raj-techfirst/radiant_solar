<!DOCTYPE html>
<html>

<head>
    <style>
        html body {
            font-family: Roboto, 'Segoe UI', Tahoma, sans-serif;
            font-size: 14px;
            line-height: 1.5rem;
        }

        h4,
        h2 {
            text-align: center;
        }

        table {
            width: 100%;
            border: none;
            border-spacing: 0;
            line-height: 1.2rem;
        }

        table th {
            background-color: #cccccc;
        }

        body {
            padding: 50px 50px 50px 50px;
        }

        td {
            padding: 3px;
            font-size: 12px;
        }

        @page {
            margin: 0px !important;
        }

        p {
            text-align: justify;
        }

        .page-break {
            page-break-before: always;
        }

        .footer_sign {
            position: absolute;
            bottom: 20;
            width: 80%;
            text-align: center;
        }
    </style>
</head>
<body>
    <p style="text-align: center;"><b><u>Residential Roof Top Solar Installation</u></b></p>
    <p style="text-align: center;"><b><u> Vendor Feasibility Report Format </u></b></p>
    <br />
    <ol>
        <li>Name of the Consmer : <b>{{ $order_data->consumer_name }}</b></li>
        <li>Discom Consumer ID : <b>{{ $order_data->consumer_number }}</b></li>
        <li>Discom ID : <b>{{ $order_data->subDivisionPDF->name }}</b></li>
        <li>PM Surya Shakti Portal ID : <b>{{ $order_data->ragistration_number }}</b></li>
        <li>Jan Samarth ID : <b>{{ $loandata->application_no ?? '_________________' }}</b></li>
        <li>Address for installation : <b>{{ $order_data->address }}</b></li>
        <li>District of Installation : <b>{{ $order_data->district->name ?? '_____________' }}</b></li>
        <li>State of Installation : <b>{{ $order_data->district->state->state_name ?? '_____________' }}</b></li>
        <li>Pin code of Installation : <b>{{ $order_data->pin_code }}</b></li>
        <li>OEM Name : _______________________________________________</li>
        <li>Channel partner If Any : <b>{{ env('APP_NAME') }}</b></li>
        <li>EPC Contractor Address : <b>{{ env('APP_OWNER_ADDRESS_NO_BR') }}</b></li>
       <li>EPC contractor Bank details :
            <br/>
                <span>A/c No : <b>{{ $bank_data->account_number ?? '' }} </b></span>&nbsp; &nbsp;
                <span>IFSC Code : <b>{{ $bank_data->ifsc_number ?? '' }} </b></span>
        </li>
        <li>RTS Capacity In KW Applied : <b>{{ number_format($order_data->register_kw, 3, '.', '') }} KW</b> </li>
        <li>Actual RTS Capacity to be installed : <b>{{ number_format($order_data->register_kw, 3, '.', '') }} KW</b>
        </li>
        <li>Is the vendor registered in MNRE Portal : <b>Yes</b> / No
            <br /><b><i>(Note : Only vendors registered in MNRE portal will be allowed)</i></b>
        </li>
        <li>Feasibility Report Status
            <br />
            <span><b>Feasible ( YES )</b></span> <span style="margin-left:150px;"> Not Feasible </span>
        </li>
        <li>Project Cost (all Inclusive) : <b>{{ number_format($order_data->total_amount, 2) }}</b></li>
        <li>Site Layout –Images (2-4 images to be uploaded )</li>
    </ol>

    <br /><br />
    @php
        $imagePath = public_path('img/stamp.png'); // Adjust the path as needed
        $imageData = base64_encode(file_get_contents($imagePath));
        $imageMime = mime_content_type($imagePath);
        $imageSrc = 'data:' . $imageMime . ';base64,' . $imageData;
    @endphp

    <p style="text-align: right;"><img src="{{ $imageSrc }}" /> <br/><b>Authorised Signatory of the vendor with Stamp</b></p>

    <div class="page-break"></div>


    <p style="text-align: center;"><b>Guidance Checklist and Consumer Education for verification of adequacy on
            Environmental, Health, and Safety
            (EHS) requirements during appraisal and monitoring</b></p>
    <p style="text-align: center;"><b>(Installation and Operation phases) of an individual project funded by SBI under
            the
            <br /> <u>Additional Financing:
                Rooftop Solar Program for Residential Sector </u>
        </b></p>

    <h4 style="text-align: left;">A. <u>Guidance Checklist for EPC / Project Developer</u><br />
        <u>Go / No-Go Criteria</u>
    </h4>

    <table border=1>

        <tr>
            <th style="width:60px;">S. No.</th>
            <th>EHS Requirement of GRPV Program</th>
            <th style="width:100px;">Yes/ No</th>
        </tr>
        <tr>
            <td>1.</td>
            <td>Confirm that Roofing material where the rooftop solar system is installed does not contain any
                carcinogenic material like broken or dilapidated Asbestos.</td>
            <td></td>
        </tr>

    </table>
    <br /><br />
    <table border=1>
        <thead>
            <tr>
                <th style="width:60px;">S. No.</th>
                <th>EHS Requirements of GRPV Program</th>
                <th>Status (State Yes/No/Not Applicable)</th>
                <th>Guidance for ensuring compliance of EHS requirements by SBI</th>
                <th>Review and Monitoring by SBI for adequacy and compliance of EHS requirements</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="5" style="text-align: center;"><strong>Proposal Appraisal Phase</strong></td>
            </tr>
            <tr>
                <td>1.</td>
                <td>Whether the GRPV proposal requires lopping/pruning of tree branches to ensure a shadow-free area on
                    the
                    roof. If yes, state whether permissions are obtained from competent authorities for periodic
                    lopping/pruning of trees</td>
                <td>Not Applicable (if no trees) / Yes (if required</td>
                <td>If yes, check validity and conditions imposed on proponent by a competent authority, if any. If not,
                    ensure first disbursement is released subject to the submission of valid permissions for loping
                    /Pruning
                    of trees.</td>
                <td>Review compliance with permissions including conditions if any by proponent.</td>
            </tr>
            <tr>
                <td>2.</td>
                <td>Whether access is available on a 24 X 365 (all days of the year irrespective of public holidays and
                    Sundays).</td>
                <td>Yes</td>
                <td>If not, seek details of alternative safe access along with permission from owner.</td>
                <td>Review the safety of the alternate access to the roof through site inspections.</td>
            </tr>
        </tbody>
    </table>

             <p style="text-align:right;"><img src="{{ $imageSrc }}" /></p>

    <div class="page-break"></div>
<table border=1>
    <thead>
            <tr>
                <th style="width:60px;">S. No.</th>
                <th>EHS Requirements of GRPV Program</th>
                <th>Status (State Yes/No/Not Applicable)</th>
                <th>Guidance for ensuring compliance of EHS requirements by SBI</th>
                <th>Review and Monitoring by SBI for adequacy and compliance of EHS requirements</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>3.</td>
                <td>Whether structural safety of the building, present condition of roof for leakages and/or cracks and
                    adequacy of roof drainage has been assessed</td>
                <td>Yes</td>
                <td>Seek a structural safety and roof condition certificate from a certified/approved Chartered Engineer
                    /
                    Architect/ Competent person along with an action plan for rectifications and responsibilities if any
                    required. If not, ensure the certificate is submitted by proponent prior to the first disbursement
                    of
                    the loan.</td>
                <td>Check the validity, review the adequacy of arrangements.</td>
            </tr>

            <tr>
                <td>4.</td>
                <td>Whether the consent<sup>1</sup> from residents / owners/ general body has been secured? Whether the
                    residents are informed about the timelines of the construction process?</td>
                <td>Yes</td>
                <td>If yes, please verify the consent document arrangements for maintaining secure and non-intrusive
                    access
                    to the installation site made and agreed with roof user/RWA. If not, ensure the first disbursement
                    is
                    released subject to the submission of a valid document. The developer should also submit the
                    timelines
                    for construction activities</td>
                <td>Review compliance with permissions by project proponent through site inspection.</td>
            </tr>
            <tr>
                <td>5.</td>
                <td>Whether proposal includes estimated water requirements for washing of panels and dependable
                    arrangements
                    to draw or share water from the same water connection or overhead tanks with owner of the building
                </td>
                <td>Yes</td>
                <td>Seek details of water requirements and its sources along with required permissions from competent
                    authorities, if any required.</td>
                <td>Review the adequacy of arrangements through monitoring.</td>
            </tr>
            <tr>
                <td>6.</td>
                <td>Does the loan include financial assistance for batteries?</td>
                <td>No (if no battery)</td>
                <td>If yes, ensure that undertaking is available for compliance with current Batteries (Management)
                    Rules
                    2021 and amendments thereof</td>
                <td>Check whether an agreement with authorized recycler is in place.</td>
            </tr>
        </tbody>
    </table>


    <br /><br /><br />



    <p>
        _______________________<br /><sup>1</sup> Include an arrangement for maintaining secure and non-intrusive access
        to the installation site made
        and agreed with roof user/RWA.</p>


    <p style="text-align:right;"><img src="{{ $imageSrc }}" /></p>

    <div class="page-break"></div>

    <table border=1>
        <tr>
            <th style="width:60px;">S. No.</th>
            <th>EHS Requirements of GRPV Program</th>
            <th>Status (State Yes/No/Not Applicable)</th>
            <th>Guidance for ensuring compliance of EHS requirements by SBI</th>
            <th>Review and Monitoring by SBI for adequacy and compliance of EHS requirements</th>
        </tr>
        <tr>
            <td colspan="5" style="text-align: center;"><strong>Installation and Operation Phase</strong></td>
        </tr>
        <tr>
            <td>7.</td>
            <td>Electrical Safety Approval: Whether earthing of all plant and equipment / components under GRPV as per
                Indian Electricity Act, 1956 and amended up to 2000 has been made and tested by an approved competent
                agency</td>
            <td>Yes</td>
            <td>Seek certification from Chief Electrical Inspector to Government (CIG), if applicable for the rooftop
                solar system size as defined in the respective state (or central) regulations.</td>
            <td>The undertaking will be taken from the proponent for compliance of the condition.</td>
        </tr>
    </table>


    <br /><br />
    <p tyle="text-align: left;font-size:16px;"><b>B. Safety guidelines to be adhered to by Installer / supplier / RESCO
            developer.</b>
        <br />(only Advisory in nature)
    </p>

    <table border=1>
        <tr>
            <th style="width:60px;">S. No.</th>
            <th>EHS Requirements of GRPV Program</th>
            <th>Status (State Yes/No/Not Applicable)</th>
            <th>Guidance for ensuring compliance of EHS requirements by SBI</th>
            <th>Review and Monitoring by SBI for adequacy and compliance of EHS requirements</th>
        </tr>
        <tr>
            <td>1.</td>
            <td>Whether the proponent / installer has accreditation of ISO 14000, OHSAS 18001 or has received any
                recognitions for environmentally friendly initiatives or best EHS practices</td>
            <td>Not Applicable (if not available)</td>
            <td>If yes, seek details of valid certifications and or recognitions. Accreditation(s) give an indication to
                institutional the capacity of the proponent to EHS requirements.</td>
            <td></td>
        </tr>
        <tr>
            <td>2.</td>
            <td>All safety provisions like provision of rubber mats, electric shock chart, first aid box, fire
                extinguishers to handle all types of fire (ABC type of required capacity), sand buckets, etc. should be
                provided/installed at appropriate locations</td>
            <td>Yes</td>
            <td>Seek details of safety measures/provisions mandatorily provided prior to testing, trial run and
                commercial operations of GRPV facility.</td>
            <td>Assess adequacy and review the safety provisions including exit routes provided and procedures followed
                during site inspections and monitoring.</td>
        </tr>
    </table>
 <p style="text-align:right;"><img src="{{ $imageSrc }}" /></p>

    <div class="page-break"></div>

    <table border=1>
        <tr>
            <th style="width:60px;">S. No.</th>
            <th>EHS Requirements of GRPV Program</th>
            <th>Status (State Yes/No/Not Applicable)</th>
            <th>Guidance for ensuring compliance of EHS requirements by SBI</th>
            <th>Review and Monitoring by SBI for adequacy and compliance of EHS requirements</th>
        </tr>
        <tr>
            <td>3.</td>
            <td>The provisions to provide safety wear, like boots, hard hats (helmets), gloves, safety belts for
                personnel while working at heights among others have been included in the proposal.</td>
            <td>Yes</td>
            <td>Seek details of safety measures/provisions mandatorily provided to all workforce deployed on-site to
                ensure the safety of personnel at work.</td>
            <td>Assess adequacy and review the safety provisions provided and procedures followed during site
                inspections and periodic monitoring.</td>
        </tr>
        <tr>
            <td>4.</td>
            <td>All personnel deployed for Installation / Operation and Maintenance are provided with basic training in
                first aid and firefighting.</td>
            <td>Yes</td>
            <td>An undertaking from the proponent that they will ensure that personnel deployed for Installation / O&M
                has a basic knowledge about first aid and fire- fighting instruments</td>
            <td></td>
        </tr>
        <tr>
            <td>5.</td>
            <td>All personnel deployed for Installation / Operation and Maintenance (Unskilled, semiskilled and skilled)
                should be paid at minimum wages as per applicable Minimum Wages Act of Government of India.</td>
            <td>Yes</td>
            <td>An undertaking from the Proponent, that they will ensure compliance of applicable Minimum Wages Act.
            </td>
            <td></td>
        </tr>
        <tr>
            <td>6.</td>
            <td>All personnel deployed for Installation / O&M are covered under workmen compensation insurance policy,
                EPF (Employee Provident Fund) Act, Gratuity Act etc. as may be applicable or relevant</td>
            <td>Yes</td>
            <td>An undertaking from proponent they will ensure that all personnel deployed for Installation/ O&M
                personnel will be covered with workmen compensation insurance policy and are provided with benefits of
                any other applicable acts.</td>
            <td>The adequacy of insurances to be checked.</td>
        </tr>
        <tr>
            <td>7.</td>
            <td>End consumers have been sensitized to the potential safety issues in installing solar rooftop plants
            </td>
            <td>Yes</td>
            <td>Share material about potential safety hazards, and likely restriction to activity in the areas where
                plant and machinery are to be installed/stored.</td>
            <td>Review compliance of Safety measures through site inspection.</td>
        </tr>
    </table>




    <br /> <p style="text-align:right;"><img src="{{ $imageSrc }}" /></p>

    <p style="text-align: right;"><strong>Authorised Signatory of the vendor with Stamp</strong></p>






</body>

</html>
