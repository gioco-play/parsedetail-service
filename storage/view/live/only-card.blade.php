@extends(\App\Helper\Constant\ParseMode::LIVE_DEFAULT)

@section('game-result')
<div class="result mt-5">
    <div class="above mb-4 d-flex flex-column align-items-center justify-content-center">
        <div class="mb-2">
            <!-- 結果 -->
            <div class="d-flex align-items-center">
                <div class="d-flex">
                @each ('layouts.elements.card', array_map(function ($card) use ($parse_type) {
                    return $parse_type . '-' . $card;
                }, $game_result['card']), 'card')
                </div>
            </div>
        </div>
    </div>
</div>
@endsection