@if (isset($game_result['game_name']) && $game_result['game_name'] != '')
<h1>{{ __($vendor_code . '.' . $game_result['game_name'], [], $lang) }}</h1>
@endif