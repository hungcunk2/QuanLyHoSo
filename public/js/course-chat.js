(function () {
    const app = document.getElementById('courseChatApp');
    if (!app) {
        return;
    }

    const role = app.dataset.role;
    const startUrl = app.dataset.startUrl;
    const messagesUrlTemplate = app.dataset.messagesUrlTemplate;
    const sendUrlTemplate = app.dataset.sendUrlTemplate;
    const csrfToken = app.dataset.csrf || document.querySelector('meta[name="csrf-token"]')?.content;

    const listEl = document.getElementById('chatConversationList');
    const placeholderEl = document.getElementById('chatPlaceholder');
    const activePanelEl = document.getElementById('chatActivePanel');
    const messagesEl = document.getElementById('chatMessages');
    const peerNameEl = document.getElementById('chatPeerName');
    const offeringLabelEl = document.getElementById('chatOfferingLabel');
    const composerEl = document.getElementById('chatComposer');
    const inputEl = document.getElementById('chatInput');
    const sendBtnEl = document.getElementById('chatSendBtn');
    const attachmentInputEl = document.getElementById('chatAttachmentInput');
    const attachmentPreviewEl = document.getElementById('chatAttachmentPreview');
    const attachmentPreviewNameEl = document.getElementById('chatAttachmentPreviewName');
    const attachmentClearEl = document.getElementById('chatAttachmentClear');
    const newChatSearchEl = document.getElementById('newChatSearch');
    const newChatSelectedValueEl = document.getElementById('newChatSelectedValue');
    const newChatPickerEl = document.getElementById('newChatPicker');
    const newChatPickerEmptyEl = document.getElementById('newChatPickerEmpty');
    const newChatModalEl = document.getElementById('newChatModal');
    const newChatStartBtn = document.getElementById('newChatStartBtn');

    let activeConversationId = null;
    let lastMessageId = 0;
    let pollTimer = null;

    function urlFor(template, conversationId) {
        return template.replace('__ID__', String(conversationId));
    }

    function setActiveListItem(conversationId) {
        listEl?.querySelectorAll('.course-chat__list-item').forEach(function (btn) {
            btn.classList.toggle('is-active', btn.dataset.conversationId === String(conversationId));
        });
    }

    function buildMessageHtml(msg) {
        let html = '';

        if (msg.attachment) {
            html += '<div class="course-chat__bubble-media">';
            if (msg.attachment.type === 'image' && msg.attachment.url) {
                html += '<a href="' + escapeAttr(msg.attachment.url) + '" target="_blank" rel="noopener">' +
                    '<img src="' + escapeAttr(msg.attachment.url) + '" alt="" class="course-chat__bubble-img">' +
                    '</a>';
            } else {
                const fileUrl = msg.attachment.download_url || msg.attachment.url;
                html += '<a href="' + escapeAttr(fileUrl) + '" class="course-chat__bubble-file" download>' +
                    '<i class="fas fa-file-alt"></i>' +
                    '<span>' + escapeHtml(msg.attachment.name || 'Tệp đính kèm') + '</span>' +
                    '</a>';
            }
            html += '</div>';
        }

        if (msg.body && msg.body.trim() !== '') {
            html += '<div class="course-chat__bubble-text">' +
                escapeHtml(msg.body).replace(/\n/g, '<br>') +
                '</div>';
        }

        html += '<time>' + escapeHtml(msg.created_at || '') + '</time>';

        return html;
    }

    function renderMessages(messages, appendOnly) {
        if (!appendOnly) {
            messagesEl.innerHTML = '';
        }

        messages.forEach(function (msg) {
            if (appendOnly && msg.id <= lastMessageId) {
                return;
            }

            const div = document.createElement('div');
            const isMine = msg.sender_role === role;
            div.className = 'course-chat__bubble ' + (isMine ? 'course-chat__bubble--mine' : 'course-chat__bubble--theirs');
            div.innerHTML = buildMessageHtml(msg);
            messagesEl.appendChild(div);

            if (msg.id > lastMessageId) {
                lastMessageId = msg.id;
            }
        });

        messagesEl.scrollTop = messagesEl.scrollHeight;
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function escapeAttr(text) {
        return String(text)
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    }

    function clearAttachmentSelection() {
        if (attachmentInputEl) {
            attachmentInputEl.value = '';
        }
        if (attachmentPreviewEl) {
            attachmentPreviewEl.hidden = true;
        }
        if (attachmentPreviewNameEl) {
            attachmentPreviewNameEl.textContent = '';
        }
    }

    function showAttachmentSelection(file) {
        if (!file || !attachmentPreviewEl || !attachmentPreviewNameEl) {
            return;
        }
        attachmentPreviewNameEl.textContent = file.name;
        attachmentPreviewEl.hidden = false;
    }

    async function loadMessages(markRead) {
        if (!activeConversationId) {
            return;
        }

        const url = new URL(urlFor(messagesUrlTemplate, activeConversationId), window.location.origin);
        if (lastMessageId > 0) {
            url.searchParams.set('after_id', String(lastMessageId));
        }
        if (markRead) {
            url.searchParams.set('mark_read', '1');
        }

        const response = await fetch(url.toString(), {
            headers: { Accept: 'application/json' },
            credentials: 'same-origin',
        });

        const payload = await response.json().catch(function () {
            return {};
        });

        if (!response.ok) {
            return;
        }

        if (payload.conversation) {
            peerNameEl.textContent = payload.conversation.peer_name || '—';
            offeringLabelEl.textContent = payload.conversation.offering_label || '—';
        }

        const appendOnly = lastMessageId > 0;
        renderMessages(payload.messages || [], appendOnly);

        if (markRead) {
            clearUnreadBadge(activeConversationId);
        }
    }

    function clearUnreadBadge(conversationId) {
        const item = listEl?.querySelector('[data-conversation-id="' + conversationId + '"]');
        if (!item) {
            return;
        }
        const badge = item.querySelector('.badge');
        if (badge) {
            badge.remove();
        }
    }

    function openConversation(conversationId) {
        activeConversationId = conversationId;
        lastMessageId = 0;
        messagesEl.innerHTML = '';
        clearAttachmentSelection();
        placeholderEl.hidden = true;
        activePanelEl.hidden = false;
        setActiveListItem(conversationId);

        loadMessages(true);

        if (pollTimer) {
            clearInterval(pollTimer);
        }
        pollTimer = setInterval(function () {
            loadMessages(true);
        }, 5000);
    }

    async function startConversation(payload) {
        const response = await fetch(startUrl, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            credentials: 'same-origin',
            body: JSON.stringify(payload),
        });

        const data = await response.json().catch(function () {
            return {};
        });

        if (!response.ok) {
            alert(data.message || 'Không thể tạo hội thoại.');
            return null;
        }

        return data.conversation_id;
    }

    async function ensureListItem(conversationId) {
        let item = listEl?.querySelector('[data-conversation-id="' + conversationId + '"]');
        if (item) {
            return item;
        }

        const emptyHint = document.getElementById('chatEmptyListHint');
        if (emptyHint) {
            emptyHint.remove();
        }

        item = document.createElement('button');
        item.type = 'button';
        item.className = 'course-chat__list-item';
        item.dataset.conversationId = String(conversationId);
        item.innerHTML =
            '<div class="course-chat__list-top"><strong class="course-chat__peer">Hội thoại mới</strong></div>' +
            '<div class="course-chat__offering text-muted small">Đang tải...</div>' +
            '<div class="course-chat__preview text-muted small">Chưa có tin nhắn</div>';
        listEl?.prepend(item);

        item.addEventListener('click', function () {
            openConversation(conversationId);
        });

        return item;
    }

    listEl?.addEventListener('click', function (event) {
        const btn = event.target.closest('.course-chat__list-item');
        if (!btn) {
            return;
        }
        openConversation(parseInt(btn.dataset.conversationId, 10));
    });

    attachmentInputEl?.addEventListener('change', function () {
        const file = attachmentInputEl.files && attachmentInputEl.files[0];
        if (file) {
            showAttachmentSelection(file);
        } else {
            clearAttachmentSelection();
        }
    });

    attachmentClearEl?.addEventListener('click', function () {
        clearAttachmentSelection();
    });

    composerEl?.addEventListener('submit', async function (event) {
        event.preventDefault();
        if (!activeConversationId) {
            return;
        }

        const body = (inputEl.value || '').trim();
        const file = attachmentInputEl?.files && attachmentInputEl.files[0];

        if (!body && !file) {
            return;
        }

        sendBtnEl.disabled = true;

        const formData = new FormData();
        if (body) {
            formData.append('body', body);
        }
        if (file) {
            formData.append('attachment', file);
        }

        try {
            const response = await fetch(urlFor(sendUrlTemplate, activeConversationId), {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                credentials: 'same-origin',
                body: formData,
            });

            const payload = await response.json().catch(function () {
                return {};
            });

            if (!response.ok) {
                const errMsg = payload.message ||
                    (payload.errors && Object.values(payload.errors).flat()[0]) ||
                    'Không gửi được tin nhắn.';
                alert(errMsg);
                return;
            }

            inputEl.value = '';
            clearAttachmentSelection();
            if (payload.message) {
                renderMessages([payload.message], true);
            }
        } finally {
            sendBtnEl.disabled = false;
            inputEl.focus();
        }
    });

    function foldSearchText(text) {
        return String(text || '')
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/đ/g, 'd');
    }

    function filterNewChatPicker() {
        if (!newChatPickerEl) {
            return;
        }

        const query = foldSearchText(newChatSearchEl?.value || '');
        let visibleCount = 0;

        newChatPickerEl.querySelectorAll('.course-chat__picker-item').forEach(function (item) {
            const haystack = foldSearchText(item.dataset.search || '');
            const match = query === '' || haystack.includes(query);
            item.hidden = !match;
            if (match) {
                visibleCount += 1;
            }
        });

        if (newChatPickerEmptyEl) {
            newChatPickerEmptyEl.hidden = visibleCount > 0;
        }
    }

    function selectNewChatItem(item) {
        if (!item || !newChatPickerEl) {
            return;
        }

        newChatPickerEl.querySelectorAll('.course-chat__picker-item.is-selected').forEach(function (el) {
            el.classList.remove('is-selected');
        });
        item.classList.add('is-selected');

        if (newChatSelectedValueEl) {
            newChatSelectedValueEl.value = item.dataset.value || '';
        }
    }

    function resetNewChatPicker() {
        if (newChatSearchEl) {
            newChatSearchEl.value = '';
        }
        if (newChatSelectedValueEl) {
            newChatSelectedValueEl.value = '';
        }
        if (newChatPickerEl) {
            newChatPickerEl.querySelectorAll('.course-chat__picker-item').forEach(function (item) {
                item.classList.remove('is-selected');
                item.hidden = false;
            });
        }
        if (newChatPickerEmptyEl) {
            newChatPickerEmptyEl.hidden = true;
        }
    }

    newChatSearchEl?.addEventListener('input', filterNewChatPicker);

    newChatPickerEl?.addEventListener('click', function (event) {
        const item = event.target.closest('.course-chat__picker-item');
        if (!item || item.hidden) {
            return;
        }
        selectNewChatItem(item);
    });

    newChatModalEl?.addEventListener('shown.bs.modal', resetNewChatPicker);

    newChatStartBtn?.addEventListener('click', async function () {
        const raw = newChatSelectedValueEl?.value;
        if (!raw) {
            alert('Vui lòng chọn người nhận trong danh sách.');
            return;
        }

        const parts = raw.split(':');
        const courseOfferingId = parseInt(parts[0], 10);
        const peerId = parseInt(parts[1], 10);

        const payload = { course_offering_id: courseOfferingId };
        if (role === 'student') {
            payload.teacher_id = peerId;
        } else {
            payload.student_id = peerId;
        }

        newChatStartBtn.disabled = true;

        try {
            const conversationId = await startConversation(payload);
            if (!conversationId) {
                return;
            }

            await ensureListItem(conversationId);

            const modalEl = document.getElementById('newChatModal');
            if (modalEl && window.bootstrap) {
                bootstrap.Modal.getOrCreateInstance(modalEl).hide();
            }

            openConversation(conversationId);
        } finally {
            newChatStartBtn.disabled = false;
        }
    });

    inputEl?.addEventListener('keydown', function (event) {
        if (event.key === 'Enter' && !event.shiftKey) {
            event.preventDefault();
            composerEl.requestSubmit();
        }
    });

    window.addEventListener('beforeunload', function () {
        if (pollTimer) {
            clearInterval(pollTimer);
        }
    });
})();
