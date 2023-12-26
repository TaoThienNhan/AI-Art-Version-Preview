<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />

    <meta name="application-name" content="{{ config('app.name') }}" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <meta name="description" content="Website thư viện hình ảnh do AI tạo ra thuộc sở hữu của AI Art, những ảnh này chia sẽ cho cộng đồng, mọi người có thể truy cập và tải ảnh mong muốn" />
    <meta name="keywords" content="free image, image, photo, ai image, AI, AI photo, AI Art, aiart.info.vn, ảnh của ai, ai thiết kế, thiết kế ảnh bằng ai, thư viện ảnh AI, AI tạo ảnh" />
    <meta name="author" content="Tào Thiện Nhân" />

    <title>{{ config('app.name') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            background-color: #2e303d;
        }
    </style>
</head>

<body>
@include('components.header')
    <div id="app" class="container-fluid">
        <main class="py-4">
            @yield('content')
        </main>
    </div>
@include('components.footer')
</body>
</html>
