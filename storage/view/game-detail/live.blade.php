@extends('layouts.game-detail')

@section('game-detail')

<div class="container p-5">
    @include('layouts.elements.game-name')

    @yield('game-result')

    @include('layouts.elements.round-no')

    @include('layouts.elements.play-type')
</div>

@endsection