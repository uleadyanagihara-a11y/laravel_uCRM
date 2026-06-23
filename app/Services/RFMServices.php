<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RFMServices{
    public static function rfm($subQuery, $rfmPrms, $endDate){
        //RMF分析
        //1.購買IDごとにまとめる
        $subQuery = $subQuery->groupBy('id')
        ->selectRaw('id, customer_id, customer_name, SUM(subtotal) as totalPerPurchase, created_at');

        //2.会員ごとにまとめて最終購入日、回数、合計金額を取得
        $subQuery = DB::table($subQuery)->groupBy('customer_id', 'customer_name')
        ->selectRaw('
        customer_id,
        customer_name,
        max(created_at) as recentDate,
        datediff(?, max(created_at)) as recency,
        count(customer_id) as frequency,
        sum(totalPerPurchase) as monetary', [$endDate]);

        // dd($subQuery);

        //4.会員ごとのRMFランクを計算
        // $rfmPrms = [
        //     14, 28, 60, 90, 7, 5, 3, 2, 300000, 200000, 100000, 30000
        // ];
        $subQuery = DB::table($subQuery)
        ->selectRaw('customer_id, customer_name, recentDate, recency, frequency, monetary,
        case
            when recency < ? then 5
            when recency < ? then 4
            when recency < ? then 3
            when recency < ? then 2
            else 1 end as r,
        case
            when ? <= frequency then 5
            when ? <= frequency then 4
            when ? <= frequency then 3
            when ? <= frequency then 2
            else 1 end as f,
        case
            when ? <= monetary then 5
            when ? <= monetary then 4
            when ? <= monetary then 3
            when ? <= monetary then 2
        else 1 end as m', $rfmPrms);

    Log::debug($subQuery->get());

    //５．ランクごとの数を計算する
    $totals = DB::table($subQuery)->count();

    $rCount = DB::table($subQuery)
    ->rightJoin('ranks', 'ranks.rank', '=', 'r')
    ->groupBy('rank')
    ->selectRaw('rank as r, count(r) as count')->pluck('count', 'r');

    Log::debug($rCount);

    $fCount = DB::table($subQuery)
    ->rightJoin('ranks', 'ranks.rank', '=', 'f')
    ->groupBy('rank')
    ->selectRaw('rank as f, count(f) as count')->pluck('count', 'f');

    $mCount = DB::table($subQuery)
    ->rightJoin('ranks', 'ranks.rank', '=', 'm')
    ->groupBy('rank')
    ->selectRaw('rank as m, count(m) as count')->pluck('count', 'm');

    $eachCount = [];//Vue側に渡すようの空の配列
    $rank = 5;//初期値５

    for($i = 0; $i < 5; $i++)
    {
        array_push($eachCount, [
            'rank' => $rank,
            'r' => $rCount->get($rank, 0),
            'f' => $fCount->get($rank, 0),
            'm' => $mCount->get($rank, 0),
        ]);
        $rank--;//rankを１ずつ減らす
    }
    // dd($total, $eachCount, $rCount, $fCount, $mCount);

    //6.RとFで２次元で表示してみる
    $data = DB::table($subQuery)
    ->rightJoin('ranks', 'ranks.rank', '=', 'm')
    ->groupBy('rank')
    ->selectRaw('concat("r_", rank) as rRank,
        count(case when f = 5 then 1 end ) as f_5,
        count(case when f = 4 then 1 end ) as f_4,
        count(case when f = 3 then 1 end ) as f_3,
        count(case when f = 2 then 1 end ) as f_2,
        count(case when f = 1 then 1 end ) as f_1')
    ->orderBy('rRank', 'desc')->get();

    //dd($data);
    return [$data, $totals, $eachCount];
    }}
