<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\HandlesCourseChat;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    use HandlesCourseChat;

    protected function chatRole(): string
    {
        return 'teacher';
    }

    protected function chatRoutePrefix(): string
    {
        return 'teacher.chat';
    }

    public function index(Request $request)
    {
        $teacher = $this->resolveTeacher();
        if (! $teacher) {
            abort(403, 'Không tìm thấy hồ sơ giáo viên.');
        }

        $conversations = $this->loadConversationsForRole('teacher', (int) $teacher->id);
        $newChatOptions = $this->chatService()->newChatOptionsForTeacher($teacher);

        return view('chat.index', [
            'chatRole' => 'teacher',
            'conversations' => $conversations,
            'existingConversationsByPeer' => $this->existingConversationsByPeer('teacher', (int) $teacher->id),
            'newChatOptions' => $newChatOptions,
            'startUrl' => route('teacher.chat.start'),
            'messagesUrlTemplate' => url('/teacher/chat/conversations/__ID__/messages'),
            'sendUrlTemplate' => url('/teacher/chat/conversations/__ID__/messages'),
            'layout' => 'layouts.teacher',
            'pageTitle' => 'Tin nhắn',
        ]);
    }
}
