<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Models\Dialog;
use App\Models\Message;
use App\Models\Client;
use App\Models\ClientBusinessman;
use App\Models\ClientCustomer;
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
            ->where(function ($query) use ($userId) {
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

            $lastMsg = Message::select('client_from', 'content', 'is_read')->where('dialog_id', $dialog->id)->latest()->first();
            $dialog->lastMsg = $lastMsg->content;

            $client = Client::where('id', $otherUserId)->first();
            if ($client->type == "businessman") {
                $dialog->UserPhoto = ClientBusinessman::where('client_id', $otherUserId)->value('photo');
            }
            if ($client->type == "customer") {
                $dialog->UserPhoto = ClientCustomer::where('client_id', $otherUserId)->value('photo');
            }
            $dialog->UserName = $client->name;
            $dialog->UserUuid = $client->uuid;
            $dialog->UserLogin = $client->login;

            $dialog->IsReadShown = $lastMsg->client_from == $userId ? true : false;
            $dialog->IsRead = $lastMsg->is_read ? true : false;
        }
        return $this->sendResponse($dialogs->toArray(), 'Список диалогов пользователя');
    }


    private function sendNotification($senderId, $dialog_id, $message)
    {
        $serverKey = 'AAAA9-20Vng:APA91bHLn3Efzgp1HQh92jLn5kIURWfdLy-s0kWEzyPaJuxGKrEbuvq8jAgt23zLDKCZxpTGSdnjgv7JqTg-7VEtEu_vjKiJl-9GatGVBTwg6KfqxKLdiNnhRaFcMPpdENdrE1UgjiN8';
        $dialog = Dialog::find($dialog_id);

        if ($senderId == $dialog->client_to){
            $recipientId= $dialog->client_from;
        }else{
            $recipientId= $dialog->client_to;
        }
        $recipientToken = Client::where('id', $recipientId)->value("device_token");

        $sender = Client::where('id', $senderId)->first();
        if ($sender->type == "customer") {
            $senderAvatar = ClientCustomer::where('client_id', $senderId)->value('photo');
        } else {
            $senderAvatar = ClientBusinessman::where('client_id', $senderId)->value('photo');
        }

        $url = 'https://fcm.googleapis.com/fcm/send';
        $fields = array(
            'to' => $recipientToken,
            'data' => array(
                "message" => $message,
                "phone" => $sender->phone,
                "avatar" => $senderAvatar,
                "dialog_id" => $dialog_id,
                "title" => $sender->name,
                "updated_at" => $dialog->updated_at->format('Y-m-d H:i:s'),
            ),
            'notification' => array(
                "title" => $sender->name,
                "body" => $message,
                "phone" => $sender->phone,
                "avatar" => $senderAvatar,
                "dialog_id" => $dialog_id,
                "updated_at" => $dialog->updated_at->format('Y-m-d H:i:s'),
            ),
            "priority" => "high"
        );
        $headers = array(
            'Authorization: key=' . $serverKey,
            'Content-type: Application/json'
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        $result = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($result);
        
        if ($result->failure > 0) {
            \DB::table('notification_errors')->insert([
                'error' => json_encode($result->results),
                'fields' => json_encode($fields)
            ]);
        }

        $result = ['error' => null, 'result' => "message sent"];

        return $result;
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
            return response()->json(['errors' => $validator->errors()], 400);
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

        return $this->sendResponse(["id" => $id], 'Dialog deleted successfully.');
    }

    public function sendMessage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'dialog_id' => 'required|exists:dialogs,id',
            'content' => 'required|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $userId = auth('api')->user()->id;

        $messageFile = null;

        if ($request->has('file')) {
            $file = base64_decode($request->file);

            $safeName = str_random(10) . '.png';
            $destinationPath = storage_path('app/public/messageFiles/' . $request->DialogId);

            if (!is_dir($destinationPath))
                mkdir($destinationPath, 0777, true);

            $success = file_put_contents($destinationPath . '/' . $safeName, $file);

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
        
        try{
            $this->sendNotification($userId, $request->dialog_id, $request->content);
        }
        catch (\Exception $th) {
        }

        return $this->sendResponse($message->toArray(), 'Message created successfully.');
    }
}
