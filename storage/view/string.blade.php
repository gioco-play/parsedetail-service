<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Game Detail</title>
    @if ($parse_type != '')
        <link rel="stylesheet" type="text/css" href="/css/{{ $parse_type }}.css">
    @endif
</head>
<body>
@foreach ($detail as $row)
    @foreach ($row as $k => $v)
        @switch (current(explode('_', $k)))
            @case ('string')
                {{ $v }}
                @break
            @case ('card')
                <div class="{{ $parse_type }}-{{ $v }}"></div>
                @break
            @default
                @break
        @endswitch
    @endforeach
    <br />
@endforeach
</body>
</html>