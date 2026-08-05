<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
     <meta charset="utf-8">
     <meta name="viewport" content="width=device-width, initial-scale=1">
     <title>Oops! An Error Occurred</title>
     <link rel="shortcut icon" href="{{asset('img/favicon.png')}}" />
     <style>
          body {
               margin: 0px;
               background-image: url("{{ asset('img/403page.jpg') }}");
               background-color: #f6ef7a;
               background-repeat: no-repeat;               
          }
          
          .block {
               margin-top: 80px;
               margin-left: 30px;
               justify-content: center;
               align-items: center;
               font-weight: 700;
               font-family: Verdana, Geneva, Tahoma, sans-serif;
               
          }

          .display-1 {
               margin-bottom: 5px;
               margin-top: 5px;
               font-size: 28px;
               color: #c31f27;
          }

          .display-2 {
               margin-bottom: 5px;
               margin-top: 5px;
               font-size: 22px;
               color: #000;
          }

          .display-3 {
               margin-bottom: 5px;
               margin-top: 10px;
               font-size: 14px;
               color: #000;
          }          
     </style>
</head>

<body>
     <div class="container">
          <div class="block">
               <h2 class="display-1">Oops! An Error Occurred</h2>
               <p class="display-2">The server returned a "405 Method Not Allowed".</p>
               <p class="display-3">Something is broken. Please let us know what you were doing when this error occurred. <br>We will fix it as soon as possible. Sorry for any inconvenience caused.</p>
          </div>
     </div>
</body>

</html>