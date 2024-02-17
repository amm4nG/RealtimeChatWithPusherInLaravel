<?php

namespace App\Http\Controllers;

use App\Events\MyEvent;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ChatController extends Controller
{
    public function index()
    {
        $users = User::where('id', '!=', Auth::user()->id)->get();
        return view('chat', compact(['users']));
    }

    public function show($id)
    {
        $messages = Message::where('sender_id', $id)
            ->where('receiver_id', Auth::user()->id)
            ->orWhere('sender_id', Auth::user()->id)
            ->where('receiver_id', $id)
            ->select('message', 'sender_id', 'created_at')
            ->get();
        return response()->json($messages);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'error' => $validator->errors()->first(),
            ]);
        }

        $message = new Message();
        $message->sender_id = Auth::user()->id;
        $message->receiver_id = $request->receiver_id;
        $message->message = $request->message;
        $message->save();
        
        event(new MyEvent($request->message, $request->receiver_id));

        return response()->json([
            'message' => $request->message,
        ]);
    }
}
