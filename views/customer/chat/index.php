<?php $layout = 'layouts/panel'; ?>

<style>
.chat-wrapper {
    display: flex;
    flex-direction: column;
    height: calc(100vh - 56px - 3.5rem);
    max-width: 780px;
    margin: 0 auto;
}
.chat-header {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 1rem 1.25rem;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.chat-header-info { display: flex; align-items: center; gap: .75rem; }
.chat-avatar {
    width: 40px; height: 40px; border-radius: 50%;
    background: var(--blue-dim); border: 1px solid var(--blue-glow);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.2rem;
}
.chat-name { font-size: .95rem; font-weight: 700; }
.chat-status { font-size: .78rem; color: var(--green); }

.chat-messages {
    flex: 1;
    overflow-y: auto;
    padding: .5rem;
    display: flex;
    flex-direction: column;
    gap: .75rem;
    scrollbar-width: thin;
    scrollbar-color: var(--border) transparent;
}

.msg {
    display: flex;
    gap: .6rem;
    max-width: 78%;
    animation: msgIn .3s ease;
}
@keyframes msgIn {
    from { opacity: 0; transform: translateY(8px); }
    to   { opacity: 1; transform: translateY(0); }
}
.msg.user   { align-self: flex-end; flex-direction: row-reverse; }
.msg.system { align-self: flex-start; }

.msg-avatar {
    width: 32px; height: 32px; border-radius: 50%;
    background: var(--card); border: 1px solid var(--border);
    display: flex; align-items: center; justify-content: center;
    font-size: .9rem; flex-shrink: 0; margin-top: .2rem;
}
.msg.system .msg-avatar { background: var(--blue-dim); border-color: var(--blue-glow); }

.msg-bubble {
    padding: .7rem 1rem;
    border-radius: 16px;
    font-size: .9rem;
    line-height: 1.6;
    word-break: break-word;
}
.msg.system .msg-bubble {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 4px 16px 16px 16px;
}
.msg.user .msg-bubble {
    background: var(--blue-dim);
    border: 1px solid var(--blue-glow);
    border-radius: 16px 4px 16px 16px;
}
.msg-time {
    font-size: .72rem;
    color: var(--muted);
    margin-top: .25rem;
    text-align: right;
}
.msg.system .msg-time { text-align: left; }

/* Quick replies */
.quick-replies {
    display: flex;
    flex-wrap: wrap;
    gap: .4rem;
    margin-top: .5rem;
    padding-left: 40px;
}
.quick-btn {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 20px;
    padding: .35rem .85rem;
    font-size: .82rem;
    cursor: pointer;
    color: var(--text);
    transition: background .2s, border-color .2s;
    font-family: inherit;
}
.quick-btn:hover {
    background: var(--blue-dim);
    border-color: var(--blue-glow);
    color: var(--blue);
}

/* Input area */
.chat-input-area {
    padding: 1rem 0 0;
    border-top: 1px solid var(--border);
    margin-top: .5rem;
}
.chat-input-row {
    display: flex;
    gap: .5rem;
    align-items: flex-end;
}
.chat-input {
    flex: 1;
    background: var(--input-bg);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: .75rem 1rem;
    color: var(--text);
    font-size: .9rem;
    font-family: inherit;
    resize: none;
    max-height: 120px;
    outline: none;
    transition: border-color .2s;
    line-height: 1.5;
}
.chat-input:focus { border-color: var(--blue); }
.chat-send-btn {
    background: var(--blue);
    border: none;
    border-radius: 10px;
    padding: .75rem 1.1rem;
    cursor: pointer;
    color: #0A1628;
    font-size: 1.1rem;
    transition: opacity .2s;
    flex-shrink: 0;
}
.chat-send-btn:hover { opacity: .85; }
.chat-send-btn:disabled { opacity: .4; cursor: not-allowed; }

.chat-hint { font-size: .75rem; color: var(--muted); margin-top: .5rem; }

/* Done state */
.chat-done-banner {
    background: rgba(34,197,94,.1);
    border: 1px solid rgba(34,197,94,.3);
    border-radius: 12px;
    padding: 1rem 1.25rem;
    margin-top: .75rem;
    text-align: center;
}
.chat-done-banner a {
    display: inline-block;
    margin-top: .75rem;
    background: var(--green);
    color: #0A1628;
    padding: .6rem 1.5rem;
    border-radius: 8px;
    font-weight: 600;
    text-decoration: none;
    font-size: .9rem;
}
</style>

<div class="chat-wrapper">
    <!-- Chat Header -->
    <div class="chat-header">
        <div class="chat-header-info">
            <div class="chat-avatar">🤖</div>
            <div>
                <div class="chat-name">GO! Dijital Danışman</div>
                <div class="chat-status">● Aktif</div>
            </div>
        </div>
        <div style="font-size:.8rem;color:var(--muted)">
            Proje: <strong><?= e($project['name'] ?? 'Yeni Proje') ?></strong>
        </div>
    </div>

    <!-- Messages -->
    <div class="chat-messages" id="chatMessages">
        <?php foreach ($history ?? [] as $msg): ?>
        <?php
            $isUser  = $msg['role'] === 'user';
            $time    = date('H:i', strtotime($msg['created_at']));
            $text    = nl2br(e($msg['message']));
        ?>
        <div class="msg <?= $isUser ? 'user' : 'system' ?>">
            <div class="msg-avatar"><?= $isUser ? '👤' : '🤖' ?></div>
            <div>
                <div class="msg-bubble"><?= $text ?></div>
                <div class="msg-time"><?= $time ?></div>
            </div>
        </div>
        <?php endforeach; ?>

        <!-- Typing indicator placeholder -->
        <div id="typingIndicator" style="display:none" class="msg system">
            <div class="msg-avatar">🤖</div>
            <div>
                <div class="msg-bubble" style="display:flex;gap:.3rem;align-items:center;padding:.7rem">
                    <span style="animation:bounce .8s infinite alternate;display:inline-block">●</span>
                    <span style="animation:bounce .8s .2s infinite alternate;display:inline-block">●</span>
                    <span style="animation:bounce .8s .4s infinite alternate;display:inline-block">●</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick replies placeholder -->
    <div id="quickReplies" class="quick-replies"></div>

    <!-- Input Area -->
    <?php if (($project['process_status'] ?? '') === 'draft'): ?>
    <div class="chat-input-area">
        <div class="chat-input-row">
            <textarea
                id="chatInput"
                class="chat-input"
                placeholder="Mesajınızı yazın... (Enter: gönder, Shift+Enter: yeni satır)"
                rows="1"
            ></textarea>
            <button class="chat-send-btn" id="sendBtn" title="Gönder" aria-label="Gönder">➤</button>
        </div>
        <p class="chat-hint">AI destekli danışman — tüm konuşmalar proje dosyanıza kaydedilir.</p>
    </div>
    <?php else: ?>
    <div class="chat-done-banner">
        <strong>🎉 Proje analiziniz tamamlandı!</strong>
        <p style="font-size:.85rem;color:var(--muted);margin-top:.35rem">Ekibimiz en kısa sürede size ulaşacak.</p>
        <a href="/panel/projeler/<?= e($project['uuid'] ?? '') ?>">Projeyi Görüntüle →</a>
    </div>
    <?php endif; ?>
</div>

<style>
@keyframes bounce { to { transform: translateY(-4px); } }
</style>

<script>
(function() {
    const messagesEl = document.getElementById('chatMessages');
    const inputEl    = document.getElementById('chatInput');
    const sendBtn    = document.getElementById('sendBtn');
    const typing     = document.getElementById('typingIndicator');
    const quickEl    = document.getElementById('quickReplies');
    const projectId  = <?= (int)($project['id'] ?? 0) ?>;
    const csrfToken  = '<?= csrf_token() ?>';

    // Scroll to bottom
    function scrollBottom() {
        messagesEl.scrollTop = messagesEl.scrollHeight;
    }
    scrollBottom();

    // Auto-resize textarea
    if (inputEl) {
        inputEl.addEventListener('input', () => {
            inputEl.style.height = 'auto';
            inputEl.style.height = Math.min(inputEl.scrollHeight, 120) + 'px';
        });

        inputEl.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });
    }

    if (sendBtn) sendBtn.addEventListener('click', sendMessage);

    function addMessage(role, text, time) {
        const isUser = role === 'user';
        const div = document.createElement('div');
        div.className = 'msg ' + (isUser ? 'user' : 'system');
        div.innerHTML = `
            <div class="msg-avatar">${isUser ? '👤' : '🤖'}</div>
            <div>
                <div class="msg-bubble">${text.replace(/\n/g,'<br>').replace(/\*\*(.*?)\*\*/g,'<strong>$1</strong>')}</div>
                <div class="msg-time">${time || ''}</div>
            </div>
        `;
        div.style.animation = 'msgIn .3s ease';
        typing.before(div);
        scrollBottom();
    }

    function renderQuickReplies(replies) {
        quickEl.innerHTML = '';
        if (!replies || !replies.length) return;
        replies.forEach(r => {
            const btn = document.createElement('button');
            btn.className = 'quick-btn';
            btn.textContent = r;
            btn.addEventListener('click', () => {
                if (inputEl) inputEl.value = r;
                sendMessage();
            });
            quickEl.appendChild(btn);
        });
    }

    function now() {
        const d = new Date();
        return d.getHours().toString().padStart(2,'0') + ':' + d.getMinutes().toString().padStart(2,'0');
    }

    async function sendMessage() {
        if (!inputEl) return;
        const text = inputEl.value.trim();
        if (!text) return;

        // Add user bubble
        addMessage('user', text, now());
        inputEl.value = '';
        inputEl.style.height = 'auto';
        quickEl.innerHTML = '';
        if (sendBtn) sendBtn.disabled = true;

        // Show typing
        typing.style.display = 'flex';
        scrollBottom();

        try {
            const fd = new FormData();
            fd.append('message', text);
            fd.append('project_id', projectId);
            fd.append('csrf_token', csrfToken);

            const res  = await fetch('/chat/send', { method: 'POST', body: fd });
            const json = await res.json();

            typing.style.display = 'none';

            if (json.success && json.data) {
                addMessage('system', json.data.bot_message, now());
                renderQuickReplies(json.data.quick_replies || []);

                if (json.data.is_done) {
                    setTimeout(() => { window.location.href = '/panel'; }, 2500);
                }
            } else {
                addMessage('system', json.message || 'Bir hata oluştu.', now());
            }
        } catch (e) {
            typing.style.display = 'none';
            addMessage('system', 'Bağlantı hatası. Lütfen tekrar deneyin.', now());
        } finally {
            if (sendBtn) sendBtn.disabled = false;
            if (inputEl) inputEl.focus();
        }
    }
})();
</script>
