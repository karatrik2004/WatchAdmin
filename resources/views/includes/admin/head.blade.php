<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<title>@yield('title') | {{ config('app.name') }}</title>
<meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
<meta name="csrf-token" content="{{ csrf_token() }}">
<!-- Favicon icon -->
<link rel="icon" href="{{ asset('backend/images/favicon.svg') }}" type="image/x-icon" />

<!-- font css -->
<link rel="stylesheet" href="{{ asset('backend/fonts/feather.css') }}" />
<link rel="stylesheet" href="{{ asset('backend/fonts/fontawesome.css') }}" />
<link rel="stylesheet" href="{{ asset('backend/fonts/material.css') }}" />

<!-- jquery.noty style -->
<link rel="stylesheet" href="{{ asset('backend/css/jquery.noty.css') }}" />

<!-- noty_theme_default style -->
<link rel="stylesheet" href="{{ asset('backend/css/noty_theme_default.css') }}" />

<!-- Custom css for sweetalert2 in admin pages-->
<link rel="stylesheet" href="{{ asset('backend/css/sweetalert2.min.css') }}" />
<!-- vendor css -->
<link rel="stylesheet" href="{{ asset('backend/css/style.css') }}" id="main-style-link">

<!-- custom style -->
<link rel="stylesheet" href="{{ asset('backend/css/custom.css') }}" />
<link rel="stylesheet" href="{{ asset('backend/css/jquery-ui.css') }}" />
{{--@vite(['resources/css/app.css', 'resources/js/app.js'])--}}
