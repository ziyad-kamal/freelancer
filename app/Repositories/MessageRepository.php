<?php

namespace App\Repositories;

use App\Classes\{ChatRooms, Messages};
use App\Events\MessageEvent;
use App\Http\Requests\MessageRequest;
use App\Interfaces\Repository\{FileRepositoryInterface, MessageRepositoryInterface};
use App\Models\User;
use App\Notifications\NewMessageNotification;
use App\Traits\GetCursor;
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\Support\Facades\{Auth, Cache, DB, Notification};

class MessageRepository implements MessageRepositoryInterface
{
	use GetCursor;

	// MARK: getMessages
	public function indexMessages():array
	{
		$auth_id        = Auth::id();
		$all_chat_rooms = ChatRooms::fetch(
			['messages.sender_id' => $auth_id, 'last' => 1],
			['messages.receiver_id' => $auth_id, 'last' => 1]
		)
		->latest('messages.id')
		->limit(3)
		->get();

		$messages     = [];
		$new_receiver = null;
		$chat_room_id = null;

		if ($all_chat_rooms->count() > 0) {
			$messages = Messages::index($all_chat_rooms[0]->chat_room_id);
		}

		return [
			'messages'       => $messages,
			'chat_room_id'   => $chat_room_id,
			'all_chat_rooms' => $all_chat_rooms,
			'new_receiver'   => $new_receiver,
			'show_chatroom'  => true,
		];
	}

	public function fetchMessages(int $receiver_id): array|RedirectResponse
	{
		$auth_id        = Auth::id();
		$all_chat_rooms = ChatRooms::fetch(
			['messages.sender_id' => $auth_id, 'last' => 1],
			['messages.receiver_id' => $auth_id, 'last' => 1]
		)
		->latest('messages.id')
		->limit(3);

		$selected_chat_room = ChatRooms::fetch(
			['messages.sender_id' => $auth_id, 'messages.receiver_id' => $receiver_id, 'last' => 1],
			['messages.receiver_id' => $auth_id, 'messages.sender_id' => $receiver_id, 'last' => 1]
		);

		$all_chat_rooms = $all_chat_rooms->union($selected_chat_room)->get();

		$messages     = [];
		$chat_room_id = null;
		$new_receiver = null;

		foreach ($all_chat_rooms as $chat_room) {
			if ($chat_room->receiver_id === $receiver_id) {
				$chat_room_id = $chat_room->chat_room_id;

				break;
			}
		}

		if (!$chat_room_id) {
			$chat_room_id = DB::table('chat_rooms')->insertGetId(
				[
					'owner_id'    => $auth_id,
					'created_at'  => now(),
				]
			);

			$new_receiver = DB::table('users')->find($receiver_id, ['name', 'image', 'id']);
			if (!$new_receiver) {
				return to_route('chat-rooms.index')->with('error', 'user not found');
			}

			DB::table('chat_room_user')
				->insert(
					[
						['chat_room_id' => $chat_room_id, 'user_id' => $auth_id],
						['chat_room_id' => $chat_room_id, 'user_id' => $receiver_id],
					]
				);
		} else {
			$messages = Messages::index($chat_room_id);
		}

		return [
			'messages'       => $messages,
			'chat_room_id'   => $chat_room_id,
			'all_chat_rooms' => $all_chat_rooms,
			'new_receiver'   => $new_receiver,
			'show_chatroom'  => true,
		];
	}

	public function getMessages(int $chat_room_id): array|RedirectResponse
	{
		$auth_id        = Auth::id();
		$all_chat_rooms = ChatRooms::fetch(
			['messages.sender_id' => $auth_id, 'last' => 1],
			['messages.receiver_id' => $auth_id, 'last' => 1]
		)
		->latest('messages.id')
		->limit(3);

		$messages     = [];
		$new_receiver = null;

		$chat_room = DB::table('chat_room_user')
				->where(['chat_room_id' => $chat_room_id, 'user_id' => $auth_id])->first();

		if (!$chat_room) {
			return to_route('chat-rooms.index')->with('error', 'user not found');
		}

		$messages = Messages::index($chat_room_id);

		$selected_chat_room = ChatRooms::fetch(
			['messages.chat_room_id' => $chat_room_id, 'last' => 1],
			[]
		);

		$all_chat_rooms = $all_chat_rooms->union($selected_chat_room)->get();

		return [
			'messages'       => $messages,
			'chat_room_id'   => $chat_room_id,
			'all_chat_rooms' => $all_chat_rooms,
			'new_receiver'   => $new_receiver,
			'show_chatroom'  => true,
		];
	}

	// storeMessage   #####################################
	public function storeMessage(MessageRequest $request, FileRepositoryInterface $fileRepository):void
	{
		$auth_user   = Auth::user();
		$data        = $request->safe()->only(['chat_room_id', 'text', 'receiver_id']) +
						['created_at' => now(), 'sender_id' => $auth_user->id];

		$receiver_id = $request->receiver_id;

		DB::table('messages')
			->where(
				[
					'chat_room_id' => $request->chat_room_id,
					'last'         => 1,
				]
			)
			->update(['last' => 0]);

		$message_id = DB::table('messages')->insertGetId($data);

		$files = $fileRepository->insert_file($request, 'message_files', 'message_id', $message_id);

		broadcast(new MessageEvent($data, $files))->toOthers();

		$notif_view = view('users.includes.notifications.send', compact('data'))->render();
		$user       = User::find($request->receiver_id);

		Notification::send($user, new NewMessageNotification($data, $auth_user->name, $auth_user->image, $notif_view));

		if (Cache::has('notifs_' . $receiver_id)) {
			Cache::forget('notifs_' . $receiver_id);
			Cache::increment('notifs_count_' . $receiver_id);
		}
	}

	// showMessage   #####################################
	public function showMessages(int $chat_room_id):string
	{
		$messages = Messages::index($chat_room_id);

		return view('users.includes.chat.index_msgs', compact('messages'))->render();
	}

	// get chat rooms   #####################################
	public function getChatRooms(int $message_id):array
	{
		$auth_id        = Auth::id();
		$all_chat_rooms = ChatRooms::fetch(
			['messages.sender_id' => $auth_id, 'last' => 1],
			['messages.receiver_id' => $auth_id, 'last' => 1],
			$message_id
		)
		->latest('messages.id')
		->limit(3)
		->get();

		$chat_room_id  = null;
		$new_receiver  = null;
		$messages      = [];
		$show_chatroom = false;

		$chat_room_view = view('users.includes.chat.index_chat_rooms', compact('show_chatroom', 'all_chat_rooms', 'chat_room_id'))->render();
		$chat_box_view  = view('users.includes.chat.index_chat_boxes', compact('show_chatroom', 'all_chat_rooms', 'chat_room_id', 'new_receiver', 'messages'))->render();

		return [
			'chat_rooms_view' => $chat_room_view,
			'chat_box_view'   => $chat_box_view,
		];
	}

	// showMessage   #####################################
	public function showOldMessages(Request $request, int $chat_room_id):string
	{
		$messages = Messages::index($chat_room_id, $request, true);

		return view('users.includes.chat.index_msgs', compact('messages'))->render();
	}
}
