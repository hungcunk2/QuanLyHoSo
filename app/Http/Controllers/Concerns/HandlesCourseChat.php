<?php

namespace App\Http\Controllers\Concerns;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\CourseOffering;
use App\Models\Student;
use App\Models\Teacher;
use App\Services\ChatAttachmentStorage;
use App\Services\CourseOfferingChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\File;
use Symfony\Component\HttpFoundation\Response;

trait HandlesCourseChat
{
    abstract protected function chatRole(): string;

    abstract protected function chatRoutePrefix(): string;

    protected function chatService(): CourseOfferingChatService
    {
        return app(CourseOfferingChatService::class);
    }

    protected function authorizeConversation(ChatConversation $conversation): void
    {
        $role = $this->chatRole();

        if ($role === 'student') {
            $student = $this->resolveStudent();
            if (! $student || (int) $conversation->student_id !== (int) $student->id) {
                abort(403);
            }

            return;
        }

        $teacher = $this->resolveTeacher();
        if (! $teacher || (int) $conversation->teacher_id !== (int) $teacher->id) {
            abort(403);
        }
    }

    protected function resolveStudent(): ?Student
    {
        $user = auth()->user();
        if (! $user || $user->role !== 'student') {
            return null;
        }

        return Student::query()
            ->where('email', $user->email)
            ->orWhere('mssv', $user->username)
            ->first();
    }

    protected function resolveTeacher(): ?Teacher
    {
        $user = auth()->user();
        if (! $user || $user->role !== 'teacher') {
            return null;
        }

        return Teacher::where('msgv', $user->username)->first();
    }

    protected function loadConversationsForRole(string $role, int $profileId)
    {
        $query = ChatConversation::query()
            ->with(['latestMessage', 'teacher', 'student', 'courseOffering.subject'])
            ->orderByDesc('updated_at');

        if ($role === 'student') {
            $query->where('student_id', $profileId);
        } else {
            $query->where('teacher_id', $profileId);
        }

        return $query->get()->map(function (ChatConversation $conversation) use ($role) {
            $latest = $conversation->latestMessage;
            $peer = $role === 'student' ? $conversation->teacher : $conversation->student;
            $offeringLabel = $this->chatService()->offeringLabel($conversation->courseOffering);

            return [
                'id' => $conversation->id,
                'peer_name' => $peer?->ho_ten ?? '—',
                'offering_label' => $offeringLabel,
                'preview' => $latest ? $this->messagePreview($latest) : 'Chưa có tin nhắn',
                'updated_at' => optional($conversation->updated_at)->format('d/m/Y H:i'),
                'unread' => $conversation->unreadCountForRole($role),
            ];
        });
    }

    public function startConversation(Request $request): JsonResponse
    {
        $role = $this->chatRole();

        if ($role === 'student') {
            $student = $this->resolveStudent();
            if (! $student) {
                return response()->json(['message' => 'Không tìm thấy hồ sơ sinh viên.'], 403);
            }

            $validated = $request->validate([
                'course_offering_id' => ['required', 'integer', 'exists:course_offerings,id'],
                'teacher_id' => ['required', 'integer', 'exists:teachers,id'],
            ]);

            $offering = CourseOffering::with('schedules')->findOrFail($validated['course_offering_id']);
            $teacher = Teacher::findOrFail($validated['teacher_id']);

            if (! $this->chatService()->canChat($student, $teacher, $offering)) {
                return response()->json(['message' => 'Bạn không thể nhắn giáo viên này trên học phần đã chọn.'], 403);
            }

            $conversation = $this->chatService()->findOrCreateConversation(
                (int) $student->id,
                (int) $teacher->id,
                (int) $offering->id
            );
        } else {
            $teacher = $this->resolveTeacher();
            if (! $teacher) {
                return response()->json(['message' => 'Không tìm thấy hồ sơ giáo viên.'], 403);
            }

            $validated = $request->validate([
                'course_offering_id' => ['required', 'integer', 'exists:course_offerings,id'],
                'student_id' => ['required', 'integer', 'exists:students,id'],
            ]);

            $offering = CourseOffering::with('schedules')->findOrFail($validated['course_offering_id']);
            $student = Student::findOrFail($validated['student_id']);

            if (! $this->chatService()->canChat($student, $teacher, $offering)) {
                return response()->json(['message' => 'Bạn không thể nhắn sinh viên này trên học phần đã chọn.'], 403);
            }

            $conversation = $this->chatService()->findOrCreateConversation(
                (int) $student->id,
                (int) $teacher->id,
                (int) $offering->id
            );
        }

        $conversation->touch();

        return response()->json([
            'conversation_id' => $conversation->id,
        ]);
    }

    public function fetchMessages(Request $request, ChatConversation $conversation): JsonResponse
    {
        $this->authorizeConversation($conversation);

        $role = $this->chatRole();
        $afterId = (int) $request->query('after_id', 0);

        if ($request->boolean('mark_read')) {
            $conversation->markReadForRole($role);
        }

        $messages = ChatMessage::query()
            ->where('chat_conversation_id', $conversation->id)
            ->when($afterId > 0, fn ($q) => $q->where('id', '>', $afterId))
            ->orderBy('id')
            ->limit(100)
            ->get()
            ->map(fn (ChatMessage $message) => $this->formatMessageForJson($message));

        $conversation->load(['teacher', 'student', 'courseOffering.subject']);
        $peer = $role === 'student' ? $conversation->teacher : $conversation->student;

        return response()->json([
            'conversation' => [
                'id' => $conversation->id,
                'peer_name' => $peer?->ho_ten ?? '—',
                'offering_label' => $this->chatService()->offeringLabel($conversation->courseOffering),
            ],
            'messages' => $messages,
        ]);
    }

    public function sendMessage(Request $request, ChatConversation $conversation): JsonResponse
    {
        $this->authorizeConversation($conversation);

        $validated = $request->validate([
            'body' => ['nullable', 'string', 'max:4000'],
            'attachment' => [
                'nullable',
                File::types([
                    'jpg', 'jpeg', 'png', 'gif', 'webp',
                    'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx',
                    'zip', 'rar', 'txt',
                ])->max(10240),
            ],
        ]);

        $body = trim((string) ($validated['body'] ?? ''));
        $file = $request->file('attachment');

        if ($body === '' && ! $file) {
            return response()->json([
                'message' => 'Vui lòng nhập nội dung hoặc chọn ảnh/tệp đính kèm.',
            ], 422);
        }

        $role = $this->chatRole();
        $attachmentData = [];

        if ($file) {
            $attachmentData = ChatAttachmentStorage::store($file, (int) $conversation->id);
        }

        $message = $conversation->messages()->create(array_merge([
            'sender_role' => $role,
            'body' => $body,
        ], $attachmentData));

        $conversation->touch();
        $conversation->markReadForRole($role);

        return response()->json([
            'message' => $this->formatMessageForJson($message),
        ], 201);
    }

    public function showAttachment(Request $request, ChatMessage $message): Response
    {
        $message->load('conversation');
        $this->authorizeConversation($message->conversation);

        if (! $message->hasAttachment() || ! ChatAttachmentStorage::exists($message->attachment_path)) {
            abort(404);
        }

        $filename = $message->attachment_original_name ?: basename($message->attachment_path);
        $forceDownload = $request->boolean('download') || ! $message->isImageAttachment();

        return ChatAttachmentStorage::response(
            $message->attachment_path,
            $filename,
            $message->attachment_mime,
            $forceDownload
        );
    }

    protected function messagePreview(ChatMessage $message): string
    {
        if ($message->hasAttachment()) {
            if ($message->isImageAttachment()) {
                $label = '[Ảnh]';
            } else {
                $label = '[Tệp]';
            }

            if (filled($message->body)) {
                return $label.' '.mb_strimwidth($message->body, 0, 60, '…');
            }

            return $label.' '.mb_strimwidth($message->attachment_original_name ?? '', 0, 60, '…');
        }

        return mb_strimwidth($message->body ?? '', 0, 80, '…');
    }

    protected function formatMessageForJson(ChatMessage $message): array
    {
        $attachment = null;
        if ($message->hasAttachment()) {
            $attachment = [
                'type' => $message->attachment_type,
                'name' => $message->attachment_original_name,
                'mime' => $message->attachment_mime,
                'url' => route($this->chatRoutePrefix().'.attachment', $message),
                'download_url' => route($this->chatRoutePrefix().'.attachment', [
                    'message' => $message,
                    'download' => 1,
                ]),
            ];
        }

        return [
            'id' => $message->id,
            'sender_role' => $message->sender_role,
            'body' => $message->body,
            'attachment' => $attachment,
            'created_at' => $message->created_at?->format('d/m/Y H:i'),
        ];
    }
}
