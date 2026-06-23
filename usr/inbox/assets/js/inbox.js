var CTRL_INBOX = "/" + chrLocale + "/controller/inbox";

let currentMail = null;
let inboxData = [];

// ── Cargar bandeja ────────────────────────────────
// ── Cargar bandeja ────────────────────────────────
async function loadInbox() {
    try {
        const res = await $.ajax({
            url: CTRL_INBOX,
            type: 'POST',
            data: {
                action: 'R',
                part: 'INBOX'
            },
            dataType: 'json'
        });

        if (!res.result) return;

        inboxData = res.data;
        renderList(inboxData);
        updateUnreadCount();

    } catch (e) {
        console.error('Error cargando inbox:', e);
    }
}
// ── Renderizar lista ──────────────────────────────
function renderList(data) {
    const $list = $('#inboxList');
    $list.empty();

    if (!data.length) {
        $list.html(`
            <div class="inbox-empty text-muted text-center py-5">
                <i class="fas fa-inbox fa-2x mb-2"></i>
                <p class="mb-0">Sin correos</p>
            </div>
        `);
        return;
    }

    data.forEach(mail => {
        const isClient = !!mail.cliente_id;
        const unreadCls = !mail.read ? 'unread' : '';

        $list.append(`
            <div class="inbox-item ${unreadCls}" data-id="${mail.id}">
                <div class="inbox-item-meta">
                    <span class="inbox-item-from">${escHtml(mail.from_name || mail.from_email)}</span>
                    <span class="inbox-item-date">${formatDate(mail.received_at)}</span>
                </div>
                <div class="inbox-item-subject">${escHtml(mail.subject)}</div>
                ${isClient ? `<span class="badge-client mt-1 d-inline-block">${escHtml(mail.cliente_nombre)}</span>` : ''}
            </div>
        `);
    });
}

// ── Click en item ─────────────────────────────────
$(document).on('click', '.inbox-item', function () {
    const id = $(this).data('id');
    const mail = inboxData.find(m => m.id == id);
    if (!mail) return;

    $('.inbox-item').removeClass('active');
    $(this).addClass('active');

    openMail(mail);
    markRead(id);
});

// ── Abrir correo ──────────────────────────────────
function openMail(mail) {
    currentMail = mail;

    $('#inboxEmptyState').addClass('d-none');
    $('#inboxMailView').removeClass('d-none');
    $('#inboxReplyPanel').addClass('d-none');

    $('#mailSubject').text(mail.subject);
    $('#mailFrom').text(mail.from_name ? `${mail.from_name} <${mail.from_email}>` : mail.from_email);
    $('#mailDate').text(formatDateFull(mail.received_at));

    // Cuerpo — sanitizar iframes/scripts pero dejar HTML del correo
    $('#mailBody').html(sanitizeBody(mail.body));

    // Cliente si matchea
    if (mail.cliente_id) {
        $('#clientName').text(mail.cliente_nombre || '');
        $('#clientCedula').text(mail.cliente_cedula || '');
        $('#clientEmpresa').text(mail.cliente_empresa || '');
        $('#inboxClientCard').removeClass('d-none');
    } else {
        $('#inboxClientCard').addClass('d-none');
    }
}

// ── Marcar como leído ─────────────────────────────
function markRead(id) {
    const mail = inboxData.find(m => m.id == id);
    if (!mail || mail.read) return;

    mail.read = true;
    $(`.inbox-item[data-id="${id}"]`).removeClass('unread');
    updateUnreadCount();

    $.post(CTRL_INBOX, { action: 'U', part: 'READ', id });
}

// ── Responder ─────────────────────────────────────
$('#btnReply').on('click', function () {
    if (!currentMail) return;
    $('#replyTo').text(currentMail.from_email);
    $('#replyBody').val('');
    $('#inboxReplyPanel').removeClass('d-none');
    $('#replyBody').focus();
});

$('#btnCancelReply').on('click', function () {
    $('#inboxReplyPanel').addClass('d-none');
});

$('#btnSendReply').on('click', async function () {
    const body = $('#replyBody').val().trim();
    if (!body) return;

    const $btn = $(this);
    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

    try {
        const res = await $.post(CTRL_INBOX, {
            action: 'M',
            part: 'REPLY',
            id: currentMail.id,
            body: body
        });

        if (res.result) {
            $('#inboxReplyPanel').addClass('d-none');
            alertNotify({ type: 'success', text: 'Respuesta enviada', timeout: 3000 });
        } else {
            alertNotify({ type: 'error', text: res.error || 'Error al enviar', timeout: 4000 });
        }
    } catch {
        alertNotify({ type: 'error', text: 'Error de conexión', timeout: 4000 });
    }

    $btn.prop('disabled', false).html('<i class="fas fa-paper-plane me-1"></i> Enviar');
});

// ── Buscador ──────────────────────────────────────
$('#inboxSearch').on('input', function () {
    const q = $(this).val().toLowerCase();
    const filtered = inboxData.filter(m =>
        (m.from_email || '').toLowerCase().includes(q) ||
        (m.from_name || '').toLowerCase().includes(q) ||
        (m.subject || '').toLowerCase().includes(q) ||
        (m.cliente_nombre || '').toLowerCase().includes(q)
    );
    renderList(filtered);
});

// ── Helpers ───────────────────────────────────────
function updateUnreadCount() {
    const n = inboxData.filter(m => !m.read).length;
    $('#inboxUnreadCount').text(n).toggleClass('d-none', n === 0);
}

function escHtml(str) {
    return $('<div>').text(str || '').html();
}

function sanitizeBody(html) {
    // Quitar scripts e iframes del cuerpo del correo
    return (html || '').replace(/<script[\s\S]*?<\/script>/gi, '')
        .replace(/<iframe[\s\S]*?<\/iframe>/gi, '');
}

function formatDate(dt) {
    if (!dt) return '';
    const d = new Date(dt);
    const now = new Date();
    if (d.toDateString() === now.toDateString()) {
        return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }
    return d.toLocaleDateString();
}

function formatDateFull(dt) {
    if (!dt) return '';
    return new Date(dt).toLocaleString();
}

// ── Init ──────────────────────────────────────────
$(function () {
    loadInbox();
});