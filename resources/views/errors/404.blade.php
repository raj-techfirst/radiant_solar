<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
     <meta charset="utf-8">
     <meta name="viewport" content="width=device-width, initial-scale=1">
     <title>Not Found</title>
     <link rel="shortcut icon" href="{{asset('img/favicon.png')}}" />
     <style>
          body {
               margin: 0px;
               background-image: url("{{asset('img/404page.png') }}");
               background-color: #f6ef7a;
               background-repeat: no-repeat;
               background-size: cover;
          }

          .block {
               color: #fff;
               margin-top: 150px;
               margin-left: 20px;
               justify-content: center;
               align-items: center;
               font-weight: 700;
               font-family: Verdana, Geneva, Tahoma, sans-serif;
          }

          .display-1 {
               margin-bottom: 5px;
               margin-top: 5px;
               font-size: 18px;
          }

          .display-2 {
               margin-bottom: 5px;
               margin-top: 5px;
               font-size: 40px;
          }

          .display-3 {
               margin-bottom: 5px;
               margin-top: 10px;
               font-size: 22px;
          }

          .display-3 .button {
               border-radius: 20px;
               color: #234f95;
               text-decoration: none;
               background: #fff;
               padding: 2px 10px;
          }
     </style>
</head>

<body>
     <div class="container">
          <div class="block">
               <h2 class="display-1">ERROR 404</h2>
               <p class="display-2">Not Found</p>
               <p class="display-3">
                    @if(Request::segment(1) == 'super')
                    <a class="button" href="{{route('home')}}">Home Page</a>
                    @elseif(Request::segment(1) == 'admin')
                    <a class="button" href="{{route('home')}}">Home Page</a>
                    @elseif(Request::segment(1) == 'agent')
                    <a class="button" href="{{route('home')}}">Home Page</a>
                    @elseif(Request::segment(1) == 'student')
                    <a class="button" href="{{route('home')}}">Home Page</a>
                    @elseif(Request::segment(1) == 'agent-master')
                    <a class="button" href="{{route('home')}}">Home Page</a>
                    @else
                    <a class="button" href="{{route('home')}}">Home Page</a>
                    @endif
               </p>
          </div>
     </div>
</body>

</html>