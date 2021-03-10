@foreach ($game_result['result'] as $type => $result)
    @switch ($type)
        @case ('is_cancel')
            @if ($result != '')
            <span class="divider mr-3 ml-3"> | </span>
            <span class="ml-2">{{ __($vendor_code . '.' . $result, [], $lang) }}</span>
            @endif
            @break
        @case ('point')
            @foreach ($result as $p => $point)
            <span class="divider mr-3 ml-3"> | </span>
            <span class="ml-2">{{ __($vendor_code . '.' . $p, [], $lang) }} {{ __('detail.point', ['point' => $point], $lang) }}</span>
            @endforeach
            @break
        @case ('string')
            <span class="divider mr-3 ml-3"> | </span>
            <span class="ml-2">{{ __($result, [], $lang) }}</span>
            @break
        @default
            @break
    @endswitch
@endforeach