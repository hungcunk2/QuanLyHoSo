@php
    $chatUser = auth()->user();
    $chatRoleLabel = match ($chatUser?->role) {
        'student' => 'Sinh viên',
        'teacher' => 'Giáo viên',
        'admin' => 'Admin',
        default => 'Người dùng',
    };
    $welcomeMessage = match ($chatUser?->role) {
        'student' => 'Chào bạn, mình là trợ lý AI. Bạn có thể hỏi về lịch học, điểm, học phần đăng ký hoặc thông báo.',
        'teacher' => 'Chào thầy/cô, mình là trợ lý AI. Có thể hỏi về lịch dạy, lớp phụ trách, sinh viên và tiến độ chấm điểm.',
        'admin' => 'Chào admin, mình là trợ lý AI. Có thể hỏi về thống kê nhanh, tra cứu sinh viên, giáo viên, lớp hoặc môn học.',
        default => 'Xin chào, mình là trợ lý AI của hệ thống.',
    };
    $suggestedPrompts = match ($chatUser?->role) {
        'student' => [
            'Lịch học hôm nay của tôi là gì?',
            'Tuần này tôi có những môn nào?',
            'Điểm các môn đã chốt của tôi ra sao?',
            'Tôi đã đăng ký những học phần nào?',
            'Có thông báo nào mới dành cho sinh viên không?',
        ],
        'teacher' => [
            'Lịch dạy hôm nay của tôi là gì?',
            'Tuần này tôi dạy những môn nào?',
            'Tôi đang phụ trách những lớp học phần nào?',
            'Học phần nào của tôi chưa chốt điểm?',
            'Lớp nào đang có nhiều sinh viên nhất?',
        ],
        'admin' => [
            'Hiện có bao nhiêu sinh viên trong hệ thống?',
            'Hiện có bao nhiêu giáo viên trong hệ thống?',
            'Thống kê học phần theo học kỳ giúp tôi.',
            'Tìm sinh viên theo tên hoặc MSSV.',
            'Có thông báo nào mới trong hệ thống không?',
        ],
        default => [
            'Hệ thống này hỗ trợ những gì?',
            'Tôi có thể hỏi AI những gì?',
        ],
    };
@endphp

<div
    id="aiChatbox"
    class="ai-chatbox"
    data-endpoint="{{ route('ai.chat') }}"
    data-storage-key="iiuh-ai-chat-{{ $chatUser?->id }}-{{ $chatUser?->role }}"
    data-role-label="{{ $chatRoleLabel }}"
    data-display-name="{{ $authDisplayName ?? ($chatUser?->username ?? $chatRoleLabel) }}"
    data-welcome-message="{{ $welcomeMessage }}"
    data-suggested-prompts='@json($suggestedPrompts)'
>
    <button type="button" class="ai-chatbox__toggle" aria-expanded="false" aria-controls="aiChatboxPanel">
        <span class="ai-chatbox__toggle-icon"><i class="fas fa-robot"></i></span>
        <span class="ai-chatbox__toggle-text">AI Chat</span>
    </button>

    <section id="aiChatboxPanel" class="ai-chatbox__panel" hidden>
        <header class="ai-chatbox__header">
            <div>
                <div class="ai-chatbox__title">Trợ lý AI</div>
                <div class="ai-chatbox__subtitle">{{ $chatRoleLabel }} · Gemini</div>
            </div>
            <button type="button" class="ai-chatbox__close" aria-label="Đóng chat">
                <i class="fas fa-xmark"></i>
            </button>
        </header>

        <div class="ai-chatbox__suggestions">
            <div class="ai-chatbox__suggestions-label">Câu hỏi gợi ý</div>
            <div class="ai-chatbox__suggestions-list"></div>
        </div>

        <div class="ai-chatbox__messages" aria-live="polite"></div>

        <form class="ai-chatbox__composer">
            <textarea
                class="ai-chatbox__input"
                rows="1"
                maxlength="4000"
                placeholder="Nhập câu hỏi của bạn..."
            ></textarea>
            <div class="ai-chatbox__actions">
                <small class="ai-chatbox__hint">Enter để gửi, Shift+Enter xuống dòng</small>
                <button type="submit" class="btn btn-primary btn-sm ai-chatbox__send">Gửi</button>
            </div>
        </form>
    </section>
</div>
