@extends(\App\Helper\Constant\ParseMode::LIVE)

@section('game-result')
<div class="result mt-5">
    <div class="above mb-4 d-flex flex-column align-items-center justify-content-center">

        @foreach ($game_result['player'] as $k => $p)
        <div class="d-flex align-items-center mb-2 row w-100">
            <span class="result-word text-{{ ($k == 0) ? 'red' : 'blue'}} text-right col-3">{{ __($vendor_code . '.' . $p['name'], ['seat' => $p['seat']], $lang) }}</span>
            <div class="d-flex flex-wrap col">
                @each ('layouts.elements.card', array_map(function ($card) use ($parse_type) {
                    return $parse_type . '-' . $card;
                }, $p['card']), 'card')
            </div>
        </div>
        @endforeach

    </div>
</div>
@endsection


