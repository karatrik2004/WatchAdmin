<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="ltr">
    <head> 
        @include('includes.admin.head')  
        @section('inline-css')
        @show      
    </head>
    <body class="admin-dashboard">    
       
		<!-- Header -->
		@include('includes.admin.header')
		<!-- Header -->

        <!-- Sidebar -->
        @include('includes.admin.sidebar')
        <!-- End of Sidebar -->
         
        <!-- Content Wrapper. Contains page content -->
        <div class="pc-container">
            <div class="pcoded-content">              
                @yield('content')        
            </div>
        </div>
        <!-- /.content-wrapper -->

		@include('includes.admin.footer')     
        @include('includes.admin.message')
        @section('inline-js')
        @show
    </body>
</html>
