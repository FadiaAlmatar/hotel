<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Carbon\Carbon;

class RoomController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['index', 'show']]); //(index ,show) only available for guests
    }
    public function index() //show all rooms with status(available now,not available now)
    {
        $rooms = Room::all();
        $now = Carbon::now()->format('Y-m-d');
        foreach($rooms as $room){
            $bookings = Booking::where('room_id',$room->id)->get();
            foreach($bookings as $booking){
                if($now >= $booking->from_date && $now <= $booking->to_date){
                    $room->status = 0;//not available now
                    $room->save();
                }else{
                    $room->status = 1;//available now
                    $room->save();
                }
            }
        }
        return response()->json([
            'success' => true,
            'data' => $rooms
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
        if(! auth()->user()->isAdmin())  //only admin can create room
             return response()->json(['success'=>false, 'data' =>null, 'message' =>'unauthorized','code' => 401]);
        
        $validator = Validator::make($request->all(), [
            'room_number' => 'required|numeric',
            'count_beds' => 'required|numeric',
            'base_price' => 'required',
        ]);
        if ($validator->fails()) { //validation failure
            return response()->json(['success'=>false, 'data' =>null, 'message' =>$validator->errors()->first(),'code' => 400]);
        }
       $room = Room::create($request->all());
        return response()->json([
                'success' => true,
                'data' => $room->toArray()
            ]);
            
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Room  $room
     * @return \Illuminate\Http\Response
     */
    public function show($id)   //show specific room
    {
        $room = Room::where('id', $id)->first();
        $bookings = Booking::where('room_id',$id)->get();
        $now = Carbon::now()->format('Y-m-d');
        foreach($bookings as $booking){  //check if room available now or not
            if($now >= $booking->from_date && $now <= $booking->to_date){
                 $room->status = 0;//not available now
                 $room->save();
            }else{
                $room->status = 1;//available
                $room->save();
            }
        }
        if (!$room) {
            return response()->json([
                'success' => false,
                'message' => 'room not found '
            ], 400);
        }
        return response()->json([
            'success' => true,
            'data' => $room->toArray()
        ], 400);
    }
    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Room  $room
     * @return \Illuminate\Http\Response
     */
    public function edit(Room $room)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Room  $room
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request,$id)
    {    
        if(! auth()->user()->isAdmin())  //only admin can update room information
          return response()->json(['success'=>false, 'data' =>null, 'message' =>'unauthorized','code' => 401]);
        $validator = Validator::make($request->all(), [
            'room_number' => 'required|numeric',
            'count_beds' => 'required|numeric',
            'base_price' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['success'=>false, 'data' =>null, 'message' =>$validator->errors()->first(),'code' => 400]);
        }
        $room = Room::where('id', $id)->first();
         if (!$room) {
            return response()->json([
                'success' => false,
                'message' => 'room not found'
            ], 400);
        }
        $room = $room->update(['room_number'=>$request->room_number,
                                'count_beds'=>$request->count_beds,
                                'base_price'=>$request->base_price]);
        if ($room)
            return response()->json([
                'success' => true
            ]);
        else
            return response()->json([
                'success' => false,
                'message' => 'room can not be updated'
            ], 500);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Room  $room
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {   
        if(! auth()->user()->isAdmin()) //only admin can delete room
          return response()->json(['success'=>false, 'data' =>null, 'message' =>'unauthorized','code' => 401]);
        $room = Room::where('id', $id)->first();
        if (!$room) {
            return response()->json([
                'success' => false,
                'message' => 'room not found'
            ], 400);
        }
        if ($room->delete()) {
            return response()->json([
                'success' => true
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'room can not be deleted'
            ], 500);
        }
    }
    public function restore($id)
    { 
        if(! auth()->user()->isAdmin())  //only admin can restore room
          return response()->json(['success'=>false, 'data' =>null, 'message' =>'unauthorized','code' => 401]);
       $room =  Room::onlyTrashed()->where('id', $id)->firstOrFail();
       if ($room->restore()) {
        return response()->json([
            'success' => true
        ]);
    } else {
        return response()->json([
            'success' => false,
            'message' => 'room can not be restored'
        ], 500);
    }
    }
}