<?php

namespace App\Http\Controllers;

use App\Models\Price;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\Room;
use Carbon\Carbon;
use App\Traits\OverLapTrait;

class PriceController extends Controller
{
    use OverLapTrait;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['index', 'show']]);
    }
    public function index()
    {
        $prices = Price::all();
        return response()->json([
            'success' => true,
            'data' => $prices
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {   
        if(! auth()->user()->isAdmin()) //admin only create price
             return response()->json(['success'=>false, 'data' =>null, 'message' =>'unauthorized','code' => 401]);
        $validator = Validator::make($request->all(), [
            'start_range' => 'required|date|date_format:Y-m-d|after_or_equal:today',
            'end_range'   => 'required|date|date_format:Y-m-d|after_or_equal:start_range',
            'value'       => 'required|numeric',
            'room_id'     => 'required|exists:rooms,id',
        ]);
        if ($validator->fails()) {  //validation failure
            return response()->json(['success'=>false, 'data' =>null, 'message' =>$validator->errors()->first(),'code' => 400]);
        }
        $prices = Price::where('room_id',$request->room_id)->get();
        foreach($prices as $price){
         if($this->checkOverLap($request,$price)){  //check overlap price date ranges
            return response()->json(['success'=>false, 'data' =>null, 'message' =>'overlap date range','code' => 400]);
         }
        }
        $price = Price::create($request->all());
        return response()->json([
                'success' => true,
                'data' => $price->toArray()
            ]);
    }
    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Price  $price
     * @return \Illuminate\Http\Response
     */
    public function show($id)   //show prices for specific room
    {
         $prices = Price::where('room_id',$id)->get();
         return response()->json([
            'success' => true,
            'data' => $prices
        ], 400);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Price  $price
     * @return \Illuminate\Http\Response
     */
    public function edit(Price $price)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Price  $price
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request,$id)
    {
        if(! auth()->user()->isAdmin()) //admin only update price
            return response()->json(['success'=>false, 'data' =>null, 'message' =>'unauthorized','code' => 401]);
        $validator = Validator::make($request->all(), [
            'start_range' => 'required|date|date_format:Y-m-d|after_or_equal:today',
            'end_range'   => 'required|date|date_format:Y-m-d|after_or_equal:start_range',
            'value'       => 'required|numeric',
            'room_id'     => 'required|exists:rooms,id',
        ]);
        if ($validator->fails()) { //validation failure
            return response()->json(['success'=>false, 'data' =>null, 'message' =>$validator->errors()->first(),'code' => 400]);
        }
        $prices = Price::where('room_id',$request->room_id)->where('id','<>',$id)->get();
        foreach($prices as $price){
        if($this->checkOverLap($request,$price)){ //check overlap price date ranges
            return response()->json(['success'=>false, 'data' =>null, 'message' =>'overlap date range','code' => 400]);
         }}
        $price = Price::where('id', $id)->firstorfail();
        $price = $price->update(['start_range'=>$request->start_range,
        'end_range'=>$request->end_range,
        'value'=>$request->value,
        'room_id'=>$request->room_id]);
      
        return response()->json([
                'success' => true,
            ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Price  $price
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {   
        if(! auth()->user()->isAdmin()) //admin only delete price
            return response()->json(['success'=>false, 'data' =>null, 'message' =>'unauthorized','code' => 401]);
        $price = Price::where('id', $id)->firstorfail()->delete();
            return response()->json([
                'success' => true
            ]);
    }
}
