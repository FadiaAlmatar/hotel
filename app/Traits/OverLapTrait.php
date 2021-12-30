<?php

namespace App\Traits;
use App\Models\Price;
use Illuminate\Http\Request;

trait OverLapTrait {
    public function checkOverLap(Request $request,Price $price)
    {
            if (($request->start_range >= $price->start_range && $request->end_range <= $price->end_range)
            ||($request->end_range >=  $price->start_range && $request->end_range <=  $price->end_range)
            ||($request->start_range >=  $price->start_range && $request->start_range <=  $price->end_range)
            ||($request->start_range<=$price->start_range && $request->end_range >= $price->end_range)){
            return true;
            
            }
    }
}