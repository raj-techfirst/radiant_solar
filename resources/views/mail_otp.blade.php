<!DOCTYPE html>
<html>

<head>
    <title>Techfirst CRM</title>
</head>

<body>
    <table style="background-color:#F1F7FF; padding:10px;">
        <tr>
            <td style="text-align: center; border-radius: 2px;"><img src="https://crm.techfirstindia.com/public/img/logo.png" height="40" width="175"></td>
        </tr>
        <tr>
            @if($param == 'forget')
            <td style="text-align:center; font-size:14px">You recently requested to reset the password for your {{ $details['email'] }} account. <br /> Here is your OTP :</td>
            @elseif($param == 'register')
            <td style="text-align:center; font-size:14px">Verification is required to register your account. <br /> Here is your OTP :</td>
            @else
            <td style="text-align:center; font-size:14px">Dear User, OTP is required for your account Verification. <br /> Here is your OTP :</td>
            @endif
        </tr>
        <tr>
            <td style="text-align:center; font-weight: 700; font-size:20px; padding-top:5px; padding-bottom:5px;">{{ $details['otp'] }}</td>
        </tr>
        <tr>
            <td style="font-style:italic; text-align:center;">Techfirst CRM</td>
        </tr>
    </table>
</body>

</html>