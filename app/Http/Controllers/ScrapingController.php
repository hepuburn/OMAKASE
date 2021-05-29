<?php

namespace App\Http\Controllers;

use App\Store;
use Illuminate\Http\Request;


class ScrapingController extends Controller
{
    public function scraping(Request $request)
    {

        $keyword = $request->input('keyword');

        // 一括で取得してSQL発行回数を減らす
        $query = Store::query()->with(['reservations']);


        if (!empty($keyword)) {
            $query->where('name', 'LIKE', "%{$keyword}%")
                ->orWhere('address', 'LIKE', "%{$keyword}%");
        }
        $stores = $query->get();


        return view('scraping', compact('keyword', 'stores'));
    }

    public function show(Request $request)
    {

        $store_id = $request->get('store_id');

        $query = Store::query()->with(['reservations'])->where('id', '=', $store_id)->first();
        return view('show', compact('query'));
    }
}
