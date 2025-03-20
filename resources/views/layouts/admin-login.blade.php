<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="ltr">
    <head> 
        @include('includes.admin.head_login')  
        @section('inline-css')        
        @show      
    </head>
    <body class="bg-gradient-primary login-page">                          
        @yield('content')      
        @include('includes.admin.footer_login') 
        @include('includes.admin.message')
        @section('inline-js')
        @show
    </body>
</html>
 