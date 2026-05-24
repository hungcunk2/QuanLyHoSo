@extends($layout)

@section('title', 'Tin nhắn')
@section('page-title', $pageTitle)

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/course-chat.css') }}?v={{ @filemtime(public_path('css/course-chat.css')) }}">
@endpush

@section('content')
<div
    id="courseChatApp"
    class="course-chat"
    data-role="{{ $chatRole }}"
    data-start-url="{{ $startUrl }}"
    data-messages-url-template="{{ $messagesUrlTemplate }}"
    data-send-url-template="{{ $sendUrlTemplate }}"
    data-csrf="{{ csrf_token() }}"
>
    <div class="course-chat__shell card border-0 shadow-sm overflow-hidden">
    <div class="course-chat__layout">
        <aside class="course-chat__sidebar">
            <div class="course-chat__sidebar-head">
                <h5 class="mb-0">Hội thoại</h5>
                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#newChatModal">
                    <i class="fas fa-plus me-1"></i> Nhắn mới
                </button>
            </div>
            <div class="course-chat__list" id="chatConversationList">
                @forelse($conversations as $conv)
                    <button
                        type="button"
                        class="course-chat__list-item"
                        data-conversation-id="{{ $conv['id'] }}"
                    >
                        <div class="course-chat__list-top">
                            <strong class="course-chat__peer">{{ $conv['peer_name'] }}</strong>
                            @if($conv['unread'] > 0)
                                <span class="badge bg-danger rounded-pill">{{ $conv['unread'] }}</span>
                            @endif
                        </div>
                        <div class="course-chat__offering text-muted small">{{ $conv['offering_label'] }}</div>
                        <div class="course-chat__preview text-muted small">{{ $conv['preview'] }}</div>
                        <div class="course-chat__time text-muted small">{{ $conv['updated_at'] }}</div>
                    </button>
                @empty
                    <p class="text-muted small px-3 py-2 mb-0" id="chatEmptyListHint">Chưa có hội thoại. Bấm «Nhắn mới» để bắt đầu.</p>
                @endforelse
            </div>
        </aside>

        <section class="course-chat__main">
            <div id="chatPlaceholder" class="course-chat__placeholder">
                <i class="fas fa-comments fa-2x text-muted mb-3"></i>
                <p class="text-muted mb-0">Chọn hội thoại hoặc bắt đầu cuộc trò chuyện mới.</p>
            </div>

            <div id="chatActivePanel" class="course-chat__active" hidden>
                <header class="course-chat__header">
                    <div>
                        <h6 class="mb-0" id="chatPeerName">—</h6>
                        <small class="text-muted" id="chatOfferingLabel">—</small>
                    </div>
                </header>
                <div class="course-chat__messages" id="chatMessages"></div>
                <form class="course-chat__composer" id="chatComposer" enctype="multipart/form-data">
                    <div class="course-chat__composer-row">
                        <label class="btn btn-outline-secondary course-chat__attach" for="chatAttachmentInput" title="Đính kèm ảnh hoặc tệp">
                            <i class="fas fa-paperclip"></i>
                        </label>
                        <input
                            type="file"
                            id="chatAttachmentInput"
                            class="d-none"
                            accept="image/jpeg,image/png,image/gif,image/webp,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.zip,.rar,.txt"
                        >
                        <textarea
                            id="chatInput"
                            class="form-control"
                            rows="2"
                            maxlength="4000"
                            placeholder="Nhập tin nhắn..."
                        ></textarea>
                        <button type="submit" class="btn btn-primary course-chat__send" id="chatSendBtn" aria-label="Gửi tin nhắn">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                    <div class="course-chat__attachment-preview" id="chatAttachmentPreview" hidden>
                        <span id="chatAttachmentPreviewName"></span>
                        <button type="button" class="btn btn-sm btn-link text-danger p-0" id="chatAttachmentClear" aria-label="Bỏ tệp đính kèm">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </form>
            </div>
        </section>
    </div>
    </div>
</div>

<div class="modal fade" id="newChatModal" tabindex="-1" aria-labelledby="newChatModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="newChatModalLabel">Bắt đầu hội thoại</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>
            <div class="modal-body">
                @if(count($newChatOptions) === 0)
                    <p class="text-muted mb-0">
                        @if($chatRole === 'student')
                            Bạn chưa đăng ký học phần nào có giáo viên phân công, nên chưa thể nhắn tin.
                        @else
                            Bạn chưa được phân công dạy học phần nào có sinh viên đăng ký.
                        @endif
                    </p>
                @else
                    @php
                        $newChatOfferingsMap = [];
                        foreach ($newChatOptions as $opt) {
                            $mapPeerId = $chatRole === 'student' ? $opt['teacher_id'] : $opt['student_id'];
                            $newChatOfferingsMap[$mapPeerId] = $opt['offerings'] ?? [[
                                'course_offering_id' => $opt['course_offering_id'],
                                'label' => $opt['label'],
                            ]];
                        }
                    @endphp
                    <script type="application/json" id="chatExistingByPeer">@json($existingConversationsByPeer ?? [])</script>
                    <script type="application/json" id="newChatOfferingsMap">@json($newChatOfferingsMap)</script>
                    <label class="form-label" for="newChatSearch">
                        @if($chatRole === 'student')
                            Tìm giáo viên theo tên hoặc học phần
                        @else
                            Tìm sinh viên theo tên hoặc học phần
                        @endif
                    </label>
                    <input
                        type="search"
                        class="form-control mb-2"
                        id="newChatSearch"
                        placeholder="{{ $chatRole === 'student' ? 'Nhập tên giáo viên hoặc học phần...' : 'Nhập tên sinh viên hoặc học phần...' }}"
                        autocomplete="off"
                    >
                    <input type="hidden" id="newChatSelectedValue" value="">
                    <div class="course-chat__picker" id="newChatPicker">
                        @foreach($newChatOptions as $opt)
                            @php
                                $peerName = $chatRole === 'student' ? $opt['teacher_name'] : $opt['student_name'];
                                $offerings = $opt['offerings'] ?? [[
                                    'course_offering_id' => $opt['course_offering_id'],
                                    'label' => $opt['label'],
                                ]];
                                $peerId = $chatRole === 'student' ? $opt['teacher_id'] : $opt['student_id'];
                                $pickerValue = count($offerings) === 1
                                    ? $offerings[0]['course_offering_id'].':'.$peerId
                                    : '';
                                $searchLabels = implode(' ', array_column($offerings, 'label'));
                                $searchHaystack = mb_strtolower($peerName.' '.$searchLabels, 'UTF-8');
                            @endphp
                            <button
                                type="button"
                                class="course-chat__picker-item"
                                data-value="{{ $pickerValue }}"
                                data-peer-id="{{ $peerId }}"
                                data-search="{{ e($searchHaystack) }}"
                            >
                                <span class="course-chat__picker-name">{{ $peerName }}</span>
                                <span class="course-chat__picker-meta">{{ $opt['label'] }}</span>
                            </button>
                        @endforeach
                    </div>
                    <div class="course-chat__offering-choice mt-2" id="newChatOfferingChoice" hidden>
                        <div class="small text-muted mb-1">Chọn học phần cần nhắn:</div>
                        <div class="d-flex flex-wrap gap-2" id="newChatOfferingButtons"></div>
                    </div>
                    <p class="text-muted small mt-2 mb-0" id="newChatPickerEmpty" hidden>Không tìm thấy kết quả phù hợp.</p>
                @endif
            </div>
            @if(count($newChatOptions) > 0)
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-primary" id="newChatStartBtn">Bắt đầu</button>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/course-chat.js') }}?v={{ @filemtime(public_path('js/course-chat.js')) }}"></script>
@endpush
