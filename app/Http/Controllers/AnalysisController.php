<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class AnalysisController extends Controller
{
    public function index()
    {
       $startDate = '2022-08-20';
       $endDate = '2022-08-21';

        //RMF分析
        //1.購買IDごとにまとめる
        $subQuery = Order::betweenDate($startDate, $endDate)->groupBy('id')
        ->selectRaw('id, customer_id, customer_name, SUM(subtotal) as totalPerPurchase, created_at');

        //2.会員ごとにまとめて最終購入日、回数、合計金額を取得
        $subQuery = DB::table($subQuery)->groupBy('customer_id', 'customer_name')
        ->selectRaw('
        customer_id,
        customer_name,
        max(created_at) as recentDate,
        datediff(now(), max(created_at)) as recency,
        count(customer_id) as frequency,
        sum(totalPerPurchase) as monetary');

        // dd($subQuery);

        //4.会員ごとのRMFランクを計算
        $rfmPrms = [
            14, 28, 60, 90, 7, 5, 3, 2, 300000, 200000, 100000, 30000
        ];
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

        //dd($subQuery->get());

    //５．ランクごとの数を計算する
    $total = DB::table($subQuery)->count();

    $rCount = DB::table($subQuery)
    ->rightJoin('ranks', 'ranks.rank', '=', 'r')
    ->groupBy('rank')
    ->selectRaw('rank as r, count(r) as count')->pluck('count', 'r');
    //dd($rCount->get());

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
    //dd($total, $eachCount, $rCount, $fCount, $mCount);

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

    return Inertia::render('Analysis');
    }

    public function decile(){
        
        $startDate = '2022-08-01';
        $endDate = '2022-08-31';

         // １．購買IDごとにまとめる
        $subQuery = Order::betweenDate($startDate, $endDate)->groupBy('id')
        ->selectRaw('id, customer_id, customer_name, SUM(subtotal) as totalPerPurchase');

        // 2.会員ごとにまとめて購入金額順にソートする
        $subQuery = DB::table($subQuery)->groupBy('customer_id', 'customer_name')
        ->selectRaw('customer_id, customer_name, sum(totalPerPurchase) as total')
        ->orderBy('total', 'desc');

        // dd($subQuery);

        // ３。購入順に連番を振る
        DB::statement('set @row_num = 0;');
        $subQuery = DB::table($subQuery)
        ->selectRaw('
        @row_num:= @row_num+1 as row_num,
        customer_id,
        customer_name,
        total
        ');

        // dd($subQuery);

        // ４．全体の件数を数え、１/１０の値や合計金額を取得
        $count = DB::table($subQuery)->count();
        $total = DB::table($subQuery)->selectRaw('sum(total) as total')->get();
        $total = $total[0]->total;//構成比用

        $decile = ceil($count / 10);//10分の１の件数を変数に入れる

        $bindValues = [];
        $tempValue = 0;
        for($i = 1; $i <= 10; $i++){
            array_push($bindValues, 1 + $tempValue);
            $tempValue += $decile;
            array_push($bindValues, 1 + $tempValue);
        }

        // dd($count, $decile, $bindValues);

        //5 10分割しグループごとに数字をふる
        DB::statement('set @row_num = 0;');
        $subQuery = DB::table($subQuery)
        ->selectRaw("
            row_num,
            customer_id,
            customer_name,
            total,
            case
                when ? <= row_num and row_num < ? then 1
                when ? <= row_num and row_num < ? then 2
                when ? <= row_num and row_num < ? then 3
                when ? <= row_num and row_num < ? then 4
                when ? <= row_num and row_num < ? then 5
                when ? <= row_num and row_num < ? then 6
                when ? <= row_num and row_num < ? then 7
                when ? <= row_num and row_num < ? then 8
                when ? <= row_num and row_num < ? then 9
                when ? <= row_num and row_num < ? then 10
            end as decile
        ", $bindValues);

    //    / dd($subQuery);

        //6.グループごとの合計、平均
        $subQuery = DB::table($subQuery)->groupBy('decile')
        ->selectRaw('decile, round(avg(total)) as average, sum(total) as totalPerGroup');

        // dd($subQuery);

        //7構成比
        DB::statement("set @total = ${total} ;");
        $data = DB::table($subQuery)
        ->selectRaw('decile, 
        average, 
        totalPerGroup,
         round(100 * totalPerGroup / @total, 1) as totalRatio
        ')->get();
    }
    public function rfm(){
        $startDate = '2022-08-01';
        $endDate = '2022-08-31';

        //RMF分析
        //1.購買IDごとにまとめる
        $subQuery = Order::betweenDate($startDate, $endDate)->groupBy('id')
        ->selectRaw('id, customer_id, customer_name, SUM(subtotal) as totalPerPurchase, created_at');

        //2.会員ごとにまとめて最終購入日、回数、合計金額を取得
        $subQuery = DB::table($subQuery)->groupBy('customer_id', 'customer_name')
        ->selectRaw('
        customer_id,
        customer_name,
        max(created_at) as recentDate,
        datediff(now(), max(created_at)) as recency,
        count(customer_id) as frequency,
        sum(totalPerPurchase) as monetary');

        // dd($subQuery);

        //4.会員ごとのRMFランクを計算
        $rfmPrms = [
            14, 28, 60, 90, 7, 5, 3, 2, 300000, 200000, 100000, 30000
        ];
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

    //５．ランクごとの数を計算する
    $total = DB::table($subQuery)->count();

    $rCount = DB::table($subQuery)
    ->groupBy('r')->selectRaw('r, count(r) as count')->pluck('count', 'r');

    $fCount = DB::table($subQuery)
    ->groupBy('f')->selectRaw('f, count(f) as count')->pluck('count', 'f');

    $mCount = DB::table($subQuery)
    ->groupBy('m')->selectRaw('m, count(m) as count')->pluck('count', 'm');

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
    $data = DB::table($subQuery)->groupBy('r')->selectRaw('concat("r_", r) as rRank,
        count(case when f = 5 then 1 end ) as f_5,
        count(case when f = 4 then 1 end ) as f_4,
        count(case when f = 3 then 1 end ) as f_3,
        count(case when f = 2 then 1 end ) as f_2,
        count(case when f = 1 then 1 end ) as f_1')
    ->orderBy('rRank', 'desc')->get();

    //dd($data);
    }
}
