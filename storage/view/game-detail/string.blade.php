@extends('layouts.game-detail')

@section('game-detail')

<div class="container p-5">
    <div class="result mt-3">
    @if (!empty($game_result))
        @foreach ($game_result as $row)
            @foreach ($row as $k => $v)
                @switch (current(explode('_', $k)))
                    @case ('string')
                        {{ $v }}
                        @break
                    @case ('card')
                        @include('layouts.elements.card', ['card' => $parse_type . '-' . $v])
                        @break
                    @default
                        @break
                @endswitch
            @endforeach
            <br />
        @endforeach
        <br />
    @endif
    </div>

    <div class="detail mt-3">
    @foreach ($game_detail as $row)
        @foreach ($row as $k => $v)
            @switch (current(explode('_', $k)))
                @case ('string')
                    {{ $v }}
                    @break
                @case ('card')
                    @include('layouts.elements.card', ['card' => $parse_type . '-' . $v])
                    @break
                @default
                    @break
            @endswitch
        @endforeach
        <br />
    @endforeach
    </div>
</div>

@endsection