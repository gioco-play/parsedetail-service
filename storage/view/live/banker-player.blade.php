@extends(\App\Helper\Constant\ParseMode::LIVE)

@section('game-result')
@if (!empty($game_result['player']) && !empty($game_result['banker']))
<div class="result mt-5">
    <div class="above mb-4 d-flex justify-content-around align-items-center">
        <div>
            @foreach ($game_result['player'] as $p)
            <div class="right d-flex align-items-center mb-2">
                <span class="result-word text-blue">{{ __($vendor_code . '.' . $p['name'], ['seat' => $p['seat']], $lang) }}</span>
                <div class="d-flex ml-3">
                    @each ('layouts.elements.card', array_map(function ($card) use ($parse_type) {
                        return $parse_type . '-' . $card;
                    }, $p['card']), 'card')
                </div>
            </div>
            @endforeach
        </div>

        <div class="divider"> | </div>

        @foreach ($game_result['banker'] as $p)
        <div class="left d-flex align-items-center mb-2">
            <div class="d-flex mr-3">
                @each ('layouts.elements.card', array_map(function ($card) use ($parse_type) {
                    return $parse_type . '-' . $card;
                }, $p['card']), 'card')
            </div>
            <span class="result-word text-red">{{ __($vendor_code . '.' . $p['name'], [], $lang) }}</span>
        </div>
        @endforeach
    </div>
</div>
@endif
@endsection