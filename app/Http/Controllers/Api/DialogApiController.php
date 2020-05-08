<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Models\Dialog;
use App\Models\Message;
use App\Models\Client;
use DB;

class DialogApiController extends ApiBaseController
{
    public function index()
    {
        // $dialogs = Dialog::select('id', 'client_to', 'is_read', 'created_at', 'updated_at')->get();

        $userId = auth('api')->user()->id;

        $dialogs = Dialog::whereExists(function ($query) {
                $query->select(DB::raw(1))
                      ->from('messages')
                      ->whereRaw('messages.dialog_id = dialogs.id');
            })
            ->where(function ($query) use($userId) {
                $query->where('client_from', $userId)
                      ->orWhere('client_to', $userId);
            })
            ->orderBy('updated_at', 'desc')
            ->get();

        foreach ($dialogs as $key => &$dialog) {
            if ($dialog->client_from != $userId) {
                $otherUserId = $dialog->client_from;
            } else {
                $otherUserId = $dialog->client_to;
            }

            // $userAvatar = User::where('phone', $otherUserId)->value('avatar');
            // if ($userAvatar) {
            //     $dialog->ImageUri = 'http://alarm.api.itmit-studio.ru' . $userAvatar;
            // } else {
            //     $dialog->ImageUri = null;
            // }

            $lastMsg = Message::select('client_from','content', 'is_read')->where('dialog_id', $dialog->id)->latest()->first();
            $dialog->lastMsg = $lastMsg->content;

            $dialog->UserName = Client::where('id', $otherUserId)->value('name');

            $dialog->IsReadShown = $lastMsg->client_from == $userId ? true : false;
            $dialog->IsRead = $lastMsg->is_read ? true : false;

        }
        return $this->sendResponse($dialogs->toArray(),'Список диалогов пользователя');
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
            'client_uuid' => 'required|uuid|exists:clients,uuid'
        ]);

        if ($validator->fails()) { 
            return response()->json(['errors'=>$validator->errors()], 400);            
        }

        $userId = auth('api')->user()->id;

        $targetClient = Client::select('id', 'name')->where('uuid', $request->client_uuid)->first();

        $dialog = Dialog::where([
                ['client_from', '=', $userId],
                ['client_to', '=', $targetClient->id],
            ])
            ->orWhere([
                ['client_from', '=', $targetClient->id],
                ['client_to', '=', $userId],
            ])
            ->first();

        if (!$dialog) {
            $dialog = Dialog::create([
                'client_from' => $userId,
                'client_to' => $targetClient->id,
                'is_read' => 0
            ]);
        }

        $dialog->client_name = $targetClient->name;

        return $this->sendResponse($dialog->toArray(), 'Dialog created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $userId = auth('api')->user()->id;

        $dialogMessages = Message::where('dialog_id', $id)->get();

        foreach ($dialogMessages as $message) {
            $isTextIn = $message->client_from == $userId ? false : true;

            if ($isTextIn) {
                $message->timestamps = false;
                $message->is_read = true;
                $message->save();
            }
            $message->IsTextIn = $isTextIn;
            $message->is_read = $message->is_read ? true : false;
        }

        return $this->sendResponse($dialogMessages->toArray(), 'Dialog retrieved successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $dialog = Dialog::find($id);

        if (is_null($dialog)) {
            return $this->sendError('Dialog not found.');
        }

        Message::where('dialog_id', $id)->delete();

        $dialog->delete();

        return $this->sendResponse($id, 'Dialog deleted successfully.');
    }

    public function sendMessage(Request $request)
    {
        $validator = Validator::make($request->all(), [ 
            'dialog_id' => 'required|exists:dialogs,id',
            'content' => 'required|max:255',
        ]);

        if ($validator->fails()) { 
            return response()->json(['errors'=>$validator->errors()], 400);            
        }

        $userId = auth('api')->user()->id;

        $messageFile = null;

        if ($request->has('file')) {
            $file = base64_decode($request->file);

            $safeName = str_random(10).'.png';
            $destinationPath = storage_path('app/public/messageFiles/' . $request->DialogId);

            if( !is_dir($destinationPath) )
                mkdir( $destinationPath, 0777, true );

            $success = file_put_contents($destinationPath . '/'. $safeName, $file);

            $messageFile = '/storage/messageFiles/' . $request->DialogId . '/' . $safeName;
        }

        $message = Message::create([
            'client_from' => $userId,
            'dialog_id' => $request->dialog_id,
            'content' => $request->content,
            'file' => $messageFile
        ]);

        $dialog = Dialog::find($request->dialog_id);
        $dialog->touch();

        // $notidcationResult = $this->sendNotification($userId, $request->DialogId, $request->Text);

        return $this->sendResponse($message->toArray(), 'Message created successfully.');
    }
}
