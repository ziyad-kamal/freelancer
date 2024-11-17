<?php

namespace App\Repositories;

use App\Classes\{ChatRooms, Messages};
use App\Http\Requests\{ChatRoomRequest};
use App\Interfaces\Repository\ChatRoomRepositoryInterface;
use App\Models\User;
use App\Notifications\{AddUserToChatNotification};
use App\Traits\DatabaseCache;
use Illuminate\Http\{JsonResponse, RedirectResponse};
use Illuminate\Support\Facades\{Auth, Cache, DB};

class ChatRoomRepository implements ChatRoomRepositoryInterface
{
	use DatabaseCache;

	// MARK: indexChatroom
	public function indexChatroom():array
	{
		$auth_id        = Auth::id();
		$all_chat_rooms = ChatRooms::fetch(
			['messages.sender_id' => $auth_id, 'last' => 1],
			['messages.receiver_id' => $auth_id, 'last' => 1]
		)
		->latest('messages.id')
		->limit(4)
		->get();

		$messages     = [];
		$receiver     = null;
		$chat_room_id = null;

		if ($all_chat_rooms->count() > 0) {
			$messages = Messages::index($all_chat_rooms[0]->chat_room_id);
		}

		return [
			'messages'       => $messages,
			'chat_room_id'   => $chat_room_id,
			'all_chat_rooms' => $all_chat_rooms,
			'receiver'       => $receiver,
			'show_chatroom'  => true,
		];
	}

	// MARK: fetch
	public function fetchWithSelectedUser(int $receiver_id): array|RedirectResponse
	{
		$receiver = DB::table('users')->find($receiver_id, ['name', 'image', 'id']);
		if (!$receiver) {
			return to_route('chatrooms.index')->with('error', 'user not found');
		}

		$auth_id        = Auth::id();
		$all_chat_rooms = ChatRooms::fetch(
			['messages.sender_id' => $auth_id, 'last' => 1],
			['messages.receiver_id' => $auth_id, 'last' => 1]
		)
		->latest('messages.id')
		->limit(4);

		$selected_chat_room = ChatRooms::fetch(
			['messages.sender_id' => $auth_id, 'messages.receiver_id' => $receiver_id, 'last' => 1],
			['messages.receiver_id' => $auth_id, 'messages.sender_id' => $receiver_id, 'last' => 1]
		);

		$all_chat_rooms = $all_chat_rooms->union($selected_chat_room)->get();

		$messages     = [];
		$receiver     = null;
		$chat_room_id = null;

		foreach ($all_chat_rooms as $chat_room) {
			if ($chat_room->receiver_id === $receiver_id) {
				$chat_room_id = $chat_room->chat_room_id;

				break;
			}
		}

		if (!$chat_room_id) {
			$chat_room_id = DB::table('chat_rooms')
				->insertGetId(
					[
						'owner_id'    => $auth_id,
						'created_at'  => now(),
					]
				);

			DB::table('messages')
				->insert([
					'chat_room_id' => $chat_room_id,
					'receiver_id'  => $receiver_id,
					'sender_id'    => $auth_id,
					'chat_room_id' => $chat_room_id,
					'sender_id'    => $auth_id,
					'text'         => 'new_chat_room%',
					'created_at'   => now(),
				]);

			DB::table('chat_room_user')
				->insert([
					['chat_room_id' => $chat_room_id, 'user_id' => $auth_id],
					['chat_room_id' => $chat_room_id, 'user_id' => $receiver_id],
				]);
		} else {
			$messages = Messages::index($chat_room_id);
		}

		return [
			'messages'       => $messages,
			'chat_room_id'   => $chat_room_id,
			'all_chat_rooms' => $all_chat_rooms,
			'receiver'       => $receiver,
			'show_chatroom'  => true,
		];
	}

	//MARK: get chat rooms
	public function getChatRooms(int $message_id):array
	{
		$auth_id        = Auth::id();
		$all_chat_rooms = ChatRooms::fetch(
			['messages.sender_id' => $auth_id, 'last' => 1],
			['messages.receiver_id' => $auth_id, 'last' => 1],
			$message_id
		)
		->latest('messages.id')
		->limit(4)
		->get();

		$chat_room_id       = null;
		$receiver           = null;
		$messages           = [];
		$show_chatroom      = false;
		$searchName         = null;
		$is_chatroom_page_1 = true;

		$chat_room_view = view('users.includes.chat.index_chat_rooms', compact('show_chatroom', 'all_chat_rooms', 'chat_room_id', 'searchName', 'is_chatroom_page_1', 'receiver'))->render();
		$chat_box_view  = view('users.includes.chat.index_chat_boxes', compact('show_chatroom', 'all_chat_rooms', 'chat_room_id', 'searchName', 'receiver', 'messages'))->render();

		return [
			'chat_rooms_view' => $chat_room_view,
			'chat_box_view'   => $chat_box_view,
		];
	}

	//MARK: sendInvitation
	public function sendInvitation(ChatRoomRequest $request):JsonResponse
	{
		$chat_room_id = $request->chat_room_id;
		$receiver_id  = $request->receiver_id;
		$data         = $request->validated() + ['created_at' => now()];

		$user_in_chatroom = DB::table('chat_room_user')
			->where(['user_id' => $receiver_id, 'chat_room_id' => $chat_room_id])
			->first();

		if (!$user_in_chatroom) {
			DB::table('chat_room_user')->insert($data);
		} else {
			return response()->json(['warning_msg' => 'user already exist in chatroom'], 400);
		}

		$user         = Auth::user();
		$receiver     = User::find($request->user_id);
		$view         = view(
			'users.includes.notifications.send_user_invitation',
			compact('chat_room_id')
		)->render();

		$receiver->notify(
			new AddUserToChatNotification(
				$chat_room_id,
				$user->name,
				$user->image,
				$view
			)
		);

		$this->forgetCache($receiver_id);

		return response()->json(['success_msg' => 'you send invitation successfully']);
	}

	// MARK: acceptInvitation
	public function postAcceptInvitationChatroom(ChatRoomRequest $request):null|RedirectResponse
	{
		$auth_id      = Auth::id();
		$chat_room_id = $request->chat_room_id;

		$chat_room_user_query = DB::table('chat_room_user')
			->where(['chat_room_id' => $chat_room_id, 'user_id' => $auth_id]);

		$chat_room  = $chat_room_user_query->first();

		if (!$chat_room) {
			return to_route('chatrooms.index')->with('error', 'user not found');
		}

		$chat_room_user_query->update(['decision' => 'approved']);

		DB::table('notifications')
			->where(['data->chat_room_id' => $chat_room_id, 'notifiable_id' => $auth_id])
			->delete();

		$this->forgetCache($auth_id);
		
		return null;
	}

	// MARK: getAcceptInvitation
	public function getAcceptInvitationChatroom(int $chat_room_id):array
	{
		$auth_id = Auth::id();

		$selected_chat_room = ChatRooms::fetch(
			['messages.chat_room_id' => $chat_room_id, 'last' => 1],
			[]
		);

		$all_chat_rooms = ChatRooms::fetch(
			['messages.sender_id' => $auth_id, 'last' => 1],
			['messages.receiver_id' => $auth_id, 'last' => 1]
		)
		->latest('messages.id')
		->limit(4);

		$all_chat_rooms = $all_chat_rooms->union($selected_chat_room)->get();
		$messages       = Messages::index($chat_room_id);

		return [
			'messages'       => $messages,
			'chat_room_id'   => $chat_room_id,
			'all_chat_rooms' => $all_chat_rooms,
		];
	}

	// MARK: refuseInvitation
	public function refuseInvitationChatroom(ChatRoomRequest $request): null|JsonResponse
	{
		$chat_room_id = $request->chat_room_id;
		$auth_id      = Auth::id();

		$chat_room_user_query = DB::table('chat_room_user')
			->where(['chat_room_id' => $chat_room_id, 'user_id' => $auth_id]);

		$chat_room_user = $chat_room_user_query->first();

		if (!$chat_room_user) {
			return response()->json(['chatroom not found'], 404);
		}

		$chat_room_user_query->delete();

		DB::table('notifications')
			->where(['data->chat_room_id' => $chat_room_id, 'notifiable_id' => $auth_id])
			->delete();

		$this->forgetCache($auth_id);

		return null;
	}
}
