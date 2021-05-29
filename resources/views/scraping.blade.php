@extends('layouts.app')

@section('content')
<main role="main">

    <section class="jumbotron text-center">
        <h3 class="search">店舗名・住所で検索</h3>
        <div class="form-group">
            <form action="{{url('/')}}" method="GET">
                <p><input type="text" name="keyword" value="{{$keyword}}"></p>
                <p><input type="submit" class="btn btn-primary" value="検索"></p>
            </form>
        </div>
    </section>

    <div class="album py-5 bg-light">
        <div class="container">
            <div class="row">
                @if($stores->count())
                @foreach ($stores as $store)
                <div class="col-md-13">
                    <div class="card mb-4 box-shadow">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">[店舗名]{{ $store->name }}</li>
                            <li class="list-group-item">[住所]{{ $store->address }}</li>
                        </ul>
                        <div class="card-body">
                            @forelse ($store->reservations as $reservation)
                            <span>[{{$reservation->date}}]</span>
                            @empty
                            <span>予約日がありません</span>
                            @endforelse
                        </div>
                    </div>
                </div>
                @endforeach
                @else
                <p>見つかりませんでした。</p>
                @endif
            </div>
        </div>
    </div>
    </div>

</main>
@endsection