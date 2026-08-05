<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
     <meta charset="utf-8">
     <meta name="viewport" content="width=device-width, initial-scale=1">
     <title>Forbidden</title>
     <link rel="shortcut icon" href="{{asset('img/favicon.png')}}" />
     <style>
          body {
               margin: 0px;
               background-image: url("{{ asset('img/403page.jpg') }}");
               background-color: #f6ef7a;
               background-repeat: no-repeat;
               background-size: cover;
          }

          .block {
               margin-top: 80px;
               margin-left: 30px;
               justify-content: center;
               align-items: center;
               font-weight: 700;
               font-family: Verdana, Geneva, Tahoma, sans-serif;
          }

          .block .button {
               border-radius: 20px;
               color: #fff;
               font-weight: 500;
               text-decoration: none;
               background: #234f95;
               padding: 2px 10px;
               font-size: 22px;
          }
     </style>
</head>

<body>
     <div class="container">
          <div class="block">
               <h1 class="pt-5" style="text-shadow: -2px 2px 0px rgb(150 150 150);"><i class="fa fa-lock fa-5x text-warning"></i> <br />Sorry! You are not authorized.</h1>
               <h5 class="pb-5">You tried to access a page you did not have prior authorization for.</h5>
               <a class="button" href="{{route('home')}}">Home Page</a>
          </div>
     </div>
</body>

</html>