<!DOCTYPE html>
<html>

<head>
    <title>Techfirst CRM</title>
</head>

<body>
    <table style="background-color:#F1F7FF; padding:10px;">
        <tr>
            <td style="text-align:center; font-size:14px">New Registration in CRM</td>
        </tr>
        <tr>
            <td>
                <table style="width: 100%;">
                    <tr>
                        <td><b>Name</b></td>
                        <td>{{ $details['name'] }} {{ $details['last_name'] }}</td>
                    </tr>
                    <tr>
                        <td><b>Company Name</b></td>
                        <td>{{ $details['company_name'] }}</td>
                    </tr>
                    <tr>
                        <td><b>Mobile</b></td>
                        <td>{{ $details['mobile'] }}</td>
                    </tr>
                    <tr>
                        <td><b>Email</b></td>
                        <td>{{ $details['email'] }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>