@extends('layouts.app')

@section('content')
<div class="main">
    <table class="table">
        <tr>
            <th>店舗名</th>
            <th>住所</th>
        </tr>
        <tr>
            <td>{{ $query->name }}</td>
            <td>{{ $query->address }}</td>
        </tr>
    </table>
    <br>
    <table class="table">
        <tr>
            <td class="yoyaku">予約可能日</td>
        </tr>
        <!-- 予約日があれば実行 -->
        @forelse ($query->reservations as $reservation)
        <tr>
            <td>{{$reservation->date}}</td>
        </tr>
        @empty
        <tr>
            <td>予約日がありません</td>
        </tr>
        @endforelse
    </table>
</div>
@endsection