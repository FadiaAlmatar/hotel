<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\Room;
use Carbon\Carbon;

class BookingController extends Controller
{
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
        $bookings = Booking::all();
        return response()->json([
            'success' => true,
            'data' => $bookings
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
        $validator = Validator::make($request->all(), [
            'from_date' => 'required|date|date_format:Y-m-d|after_or_equal:today',
            'to_date'   => 'required|date|date_format:Y-m-d|after_or_equal:from_date',
            'room_id'   => 'required|exists:rooms,id',
            'user_id'   => 'exists:users,id',
        ]);
        if ($validator->fails()) { //validation failure
            return response()->json(['success'=>false, 'data' =>null, 'message' =>$validator->errors()->first(),'code' => 400]);
        }
        $bookings = Booking::where('room_id',$request->room_id)->get();
        foreach($bookings as $booking){
            if($this->checkBooking($request,$booking)){ //check if user can book this room or not
                return response()->json(['success'=>false, 'data' =>null, 'message' =>'overlap date range','code' => 400]);
            }
        }
        $booking = Booking::create(['from_date'=>$request->from_date,
                                        'to_date'=>$request->to_date,
                                        'user_id'=>$request->user()->id,
                                        'room_id'=>$request->room_id]);
        if($booking)
            return response()->json([
                'success' => true,
                'data' => $booking->toArray()
                ]);
        else
            return response()->json([
                'success' => false,
                'message' => 'Booking not added'
            ], 500);
        
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Booking  $booking
     * @return \Illuminate\Http\Response
     */
    public function show($id)  //show specific booking
    {
        $booking = Booking::where('id',$id)->first();
        return response()->json([
           'success' => true,
           'data' => $booking
       ], 400);
    }
    public function showMyBookings(Request $request)  //show my bookings
    {
        $bookings = Booking::where('user_id',$request->user()->id)->get();
        if($bookings->isEmpty())
        return response()->json([
            'success' => true,
            'message' => 'there is no bookings yet'
        ]);
        else
        return response()->json([
           'success' => true,
           'data' => $bookings
       ], 400);
    }
    public function showRoomBookings($id)  //show bookings for specific room
    {
        if(! auth()->user()->isAdmin()) //only admin can show bookings of specific room
             return response()->json(['success'=>false, 'data' =>null, 'message' =>'unauthorized','code' => 401]);
        $bookings = Booking::where('room_id',$id)->get();
        if($bookings->isEmpty())
        return response()->json([
            'success' => true,
            'message' => 'there is no bookings for this room'
        ]);
        else
        return response()->json([
           'success' => true,
           'data' => $bookings
       ], 400);
    }
    public function showUserBookings($id)  //show bookings for specific user
    {   
        if(! auth()->user()->isAdmin()) //only admin can show bookings of specific user
             return response()->json(['success'=>false, 'data' =>null, 'message' =>'unauthorized','code' => 401]);
        $bookings = Booking::where('user_id',$id)->get();
        if($bookings->isEmpty())
        return response()->json([
            'success' => true,
            'message' => 'there is no bookings for this user'
        ]);
        else
        return response()->json([
           'success' => true,
           'data' => $bookings
       ], 400);
    }
    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Booking  $booking
     * @return \Illuminate\Http\Response
     */
    public function edit(Booking $booking)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Booking  $booking
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request,$id)
    {  
        $booking = Booking::where('id',$id)->first();
        if ($request->user()->cant('update', $booking)) {
            return response()->json(['success'=>false, 'data' =>null, 'message' =>'you can not update','code' => 403]);
        }
        $validator = Validator::make($request->all(), [
            'from_date' => 'required|date|date_format:Y-m-d|after_or_equal:today',
            'to_date'   => 'required|date|date_format:Y-m-d|after_or_equal:from_date',
            'room_id'   => 'required|exists:rooms,id',
            'user_id'   => 'exists:users,id',
        ]);
        if ($validator->fails()) { //validation failure
            return response()->json(['success'=>false, 'data' =>null, 'message' =>$validator->errors()->first(),'code' => 400]);
        }
        
        $bookings = Booking::where('room_id',$request->room_id)->where('id','<>',$id)->get();
        foreach($bookings as $booking){
        if($this->checkBooking($request,$booking)){ //check if user can book this room or not
            return response()->json(['success'=>false, 'data' =>null, 'message' =>'overlap date range','code' => 400]);
         }}
        $booking = Booking::where('id', $id)->firstorfail();
        $booking = $booking->update(['from_date'=>$request->from_date,
                                    'to_date'=>$request->to_date,
                                    'user_id'=>$request->user()->id,
                                    'room_id'=>$request->room_id]);
        if($booking)
            return response()->json([
                'success' => true,
            ]);
        else
            return response()->json([
                'success' => false,
                'message' => 'Booking not updated'
            ], 500);
        
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Booking  $booking
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    { 
        $booking = Booking::where('id',$id)->first();
        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'booking not found'
            ], 400);
        }
        if (Auth::User()->cant('delete', $booking)) {
            return response()->json(['success'=>false, 'data' =>null, 'message' =>'you can not delete','code' => 403]);
        }
        if ($booking->delete()) {
            return response()->json([
                'success' => true
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'booking can not be deleted'
            ], 500);
        }

    }
    public function checkBooking(Request $request,Booking $booking) //check overlap booking date ranges
    {
        if (($request->from_date >= $booking->from_date && $request->to_date <= $booking->to_date)
        ||($request->to_date >=  $booking->from_date && $request->to_date <=  $booking->to_date)
        ||($request->from_date >=  $booking->from_date && $request->from_date <=  $booking->to_date)
        ||($request->from_date<=$booking->from_date && $request->to_date >= $booking->to_date)){
        return true;
        }
    }

}
