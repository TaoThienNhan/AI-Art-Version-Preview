@extends('layouts.app')

@section('content')
    <div class="container-fluid pt70 pb70">
        <div id="ttn-projects-feed" class="ttn-projects-feed clearfix masonry">
            @php
                @endphp
            @forelse ($galleries as $gallery)
                <div class="ttn-project masonry-brick">
                    <a href="#">
                        <img style="width: 100%;" src="{{ env('APP_URL') . '/storage/' . $gallery->images->path }}" alt="{{ $gallery->images->name }}">
                        <h2>{{$gallery['title']}}</h2>
                    </a>
                </div>
            @empty
                <p>Không có sản phẩm nào.</p>
            @endforelse
        </div>
    </div>
@endsection
