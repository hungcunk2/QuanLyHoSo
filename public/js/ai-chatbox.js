document.addEventListener('DOMContentLoaded', function () {
    const widget = document.getElementById('aiChatbox');
    if (!widget) {
        return;
    }

    const panel = widget.querySelector('.ai-chatbox__panel');
    const toggleButton = widget.querySelector('.ai-chatbox__toggle');
    const closeButton = widget.querySelector('.ai-chatbox__close');
    const messagesEl = widget.querySelector('.ai-chatbox__messages');
    const form = widget.querySelector('.ai-chatbox__composer');
    const input = widget.querySelector('.ai-chatbox__input');
    const sendButton = widget.querySelector('.ai-chatbox__send');
    const suggestionsList = widget.querySelector('.ai-chatbox__suggestions-list');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const endpoint = widget.dataset.endpoint;
    const storageKey = widget.dataset.storageKey;
    const welcomeMessage = widget.dataset.welcomeMessage || 'Xin chào, mình là trợ lý AI của hệ thống.';
    const suggestedPrompts = parseSuggestedPrompts(widget.dataset.suggestedPrompts);

    const state = {
        isOpen: false,
        isLoading: false,
        messages: [],
    };

    function parseSuggestedPrompts(raw) {
        try {
            const parsed = JSON.parse(raw || '[]');
            return Array.isArray(parsed) ? parsed.filter((item) => typeof item === 'string' && item.trim() !== '') : [];
        } catch (error) {
            return [];
        }
    }

    function loadMessages() {
        try {
            const raw = sessionStorage.getItem(storageKey);
            if (!raw) {
                return [];
            }

            const parsed = JSON.parse(raw);
            if (!Array.isArray(parsed)) {
                return [];
            }

            return parsed
                .filter((item) => item && typeof item.content === 'string' && typeof item.role === 'string')
                .slice(-12);
        } catch (error) {
            return [];
        }
    }

    function saveMessages() {
        sessionStorage.setItem(storageKey, JSON.stringify(state.messages.slice(-12)));
    }

    function ensureWelcomeMessage() {
        if (state.messages.length === 0) {
            state.messages.push({
                role: 'assistant',
                content: welcomeMessage,
            });
        }
    }

    function escapeContent(content) {
        return content.replace(/[&<>"']/g, function (char) {
            const entities = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;',
            };

            return entities[char] || char;
        });
    }

    function renderMessages() {
        messagesEl.innerHTML = state.messages
            .map(function (message) {
                const isAssistant = message.role === 'assistant';
                const roleClass = isAssistant ? 'is-assistant' : 'is-user';
                const author = isAssistant ? 'AI' : 'Bạn';

                return (
                    '<article class="ai-chatbox__message ' + roleClass + '">' +
                        '<div class="ai-chatbox__message-author">' + author + '</div>' +
                        '<div class="ai-chatbox__message-body">' + escapeContent(message.content).replace(/\n/g, '<br>') + '</div>' +
                    '</article>'
                );
            })
            .join('');

        messagesEl.scrollTop = messagesEl.scrollHeight;
    }

    function setOpen(nextOpen) {
        state.isOpen = nextOpen;
        panel.hidden = !nextOpen;
        toggleButton.setAttribute('aria-expanded', nextOpen ? 'true' : 'false');
        widget.classList.toggle('is-open', nextOpen);

        if (nextOpen) {
            input.focus();
        }
    }

    function autoResizeTextarea() {
        input.style.height = 'auto';
        input.style.height = Math.min(input.scrollHeight, 140) + 'px';
    }

    function setLoading(isLoading) {
        state.isLoading = isLoading;
        input.disabled = isLoading;
        sendButton.disabled = isLoading;
        sendButton.textContent = isLoading ? 'Đang gửi...' : 'Gửi';

        if (suggestionsList) {
            suggestionsList.querySelectorAll('.ai-chatbox__suggestion').forEach(function (button) {
                button.disabled = isLoading;
            });
        }
    }

    async function sendMessage(content) {
        if (!endpoint || state.isLoading) {
            return;
        }

        const history = state.messages.slice(-8).map(function (message) {
            return {
                role: message.role,
                content: message.content,
            };
        });

        state.messages.push({
            role: 'user',
            content: content,
        });
        renderMessages();
        saveMessages();
        setLoading(true);

        try {
            const response = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    message: content,
                    history: history,
                }),
            });

            const payload = await response.json().catch(function () {
                return {};
            });

            if (!response.ok) {
                throw new Error(payload.message || 'Không thể gửi câu hỏi tới AI.');
            }

            state.messages.push({
                role: 'assistant',
                content: payload.reply || 'AI chưa trả về nội dung.',
            });
            saveMessages();
            renderMessages();
        } catch (error) {
            state.messages.push({
                role: 'assistant',
                content: error.message || 'Đã có lỗi xảy ra khi kết nối AI.',
            });
            saveMessages();
            renderMessages();
        } finally {
            setLoading(false);
            input.focus();
        }
    }

    state.messages = loadMessages();
    ensureWelcomeMessage();
    renderMessages();
    autoResizeTextarea();

    if (suggestionsList) {
        suggestionsList.innerHTML = suggestedPrompts
            .map(function (prompt) {
                return '<button type="button" class="ai-chatbox__suggestion">' + escapeContent(prompt) + '</button>';
            })
            .join('');

        suggestionsList.addEventListener('click', function (event) {
            const button = event.target.closest('.ai-chatbox__suggestion');
            if (!button || state.isLoading) {
                return;
            }

            const content = button.textContent.trim();
            if (!content) {
                return;
            }

            setOpen(true);
            input.value = '';
            autoResizeTextarea();
            sendMessage(content);
        });
    }

    toggleButton.addEventListener('click', function () {
        setOpen(!state.isOpen);
    });

    closeButton.addEventListener('click', function () {
        setOpen(false);
    });

    input.addEventListener('input', autoResizeTextarea);
    input.addEventListener('keydown', function (event) {
        if (event.key === 'Enter' && !event.shiftKey) {
            event.preventDefault();
            form.requestSubmit();
        }
    });

    form.addEventListener('submit', function (event) {
        event.preventDefault();

        const content = input.value.trim();
        if (!content) {
            return;
        }

        input.value = '';
        autoResizeTextarea();
        sendMessage(content);
    });
});
