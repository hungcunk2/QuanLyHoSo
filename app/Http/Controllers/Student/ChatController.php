<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\HandlesCourseChat;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    use HandlesCourseChat;

    protected function chatRole(): string
    {
        return 'student';
    }

    protected function chatRoutePrefix(): string
    {
        return 'student.chat';
    }

    public function index(Request $request)
    {
        $student = $this->resolveStudent();
        if (! $student) {
            abort(403, 'Không tìm thấy hồ sơ sinh viên.');
        }

        $conversations = $this->loadConversationsForRole('student', (int) $student->id);
        $newChatOptions = $this->chatService()->newChatOptionsForStudent($student);

        return view('chat.index', [
            'chatRole' => 'student',
            'conversations' => $conversations,
            'existingConversationsByPeer' => $this->existingConversationsByPeer('student', (int) $student->id),
            'newChatOptions' => $newChatOptions,
            'startUrl' => route('student.chat.start'),
            'messagesUrlTemplate' => url('/student/chat/conversations/__ID__/messages'),
            'sendUrlTemplate' => url('/student/chat/conversations/__ID__/messages'),
            'layout' => 'layouts.student',
            'pageTitle' => 'Tin nhắn',
        ]);
    }
}
