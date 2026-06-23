<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use App\Services\AnalysisServices;
use App\Services\DecileServices;
use App\Services\RFMServices;

class AnalysisController extends Controller{
   
    public function index(Request $request){
        
         $subQuery = Order::betweenDate($request->startDate, $request->endDate);

        if($request->type === 'perDay'){
            list($data, $labels, $totals) = AnalysisServices::perDay($subQuery);
        }

        if($request->type === 'perMonth'){
            list($data, $labels, $totals) = AnalysisServices::perMonth($subQuery);
        }

        if($request->type === 'perYear'){
            list($data, $labels, $totals) = AnalysisServices::perYear($subQuery);
        }

        if($request->type === 'decile'){
            list($data, $labels, $totals) = DecileServices::decile($subQuery);
        }

        if($request->type === 'rfm'){
            list($data, $totals, $eachCount) = RFMServices::rfm(
                $subQuery,
                $request->rfmPrms,
                $request->endDate
            );

            return response()->json([
            'data' => $data,
            'type' => $request->type,
            'eachCount' => $eachCount,
            'totals' => $totals,
            ], Response::HTTP_OK);
            }

        return response()->json([
            'data' => $data,
            'type' => $request->type,
            'labels' => $labels,
            'totals' => $totals,
        ], Response::HTTP_OK);
    }
}
