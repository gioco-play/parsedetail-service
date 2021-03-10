<div class="result mt-3">
    <div class="bottom d-flex justify-content-center align-items-center">
        @if (isset($game_result['round_no']))
        <span>{{ __('detail.round_no', [], $lang) }}：{{ $game_result['round_no'] }}</span>
        @endif

        @include('layouts.elements.game-result')

    </div>
</div>