<div class="detail mt-5">
    <table class="table table-bordered">
        <thead class="thead-light">
            <tr>
                <th scope="col">{{ __('detail.bet_time', [], $lang) }}</th>
                <th scope="col">{{ __('detail.bill_no', [], $lang) }}</th>
                <th scope="col">{{ __('detail.play_type', [], $lang) }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($game_detail as $row)
            <tr>
                <td>{{ $row['bet_time'] }}</td>
                <td>{{ $row['bet_id'] }}</td>
                <td>{{ __($row['play_type'], [], $lang) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="3" style="text-align: center;">{{ __('detail.no_data', [], $lang) }}</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>