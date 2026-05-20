<?php

/**
 * GO! Event → Listener mapping.
 * Her event için dinleyici sınıflarını döner.
 *
 * Format:
 * 'event.name' => [
 *     'GO\\Events\\Listeners\\ListenerClassName',
 * ]
 *
 * Listener sınıfları /events/listeners/ klasöründe.
 * Her listener'da public handle(array $payload): void metodu olmalı.
 *
 * Aşama 9'da SMTP ve bildirimler implement edilince listener'lar doldurulacak.
 */

return [

    // ─── Kullanıcı eventleri ──────────────────────────────────────────────────
    'user.registered' => [
        // 'GO\\Events\\Listeners\\SendWelcomeEmail',     // Aşama 9
        // 'GO\\Events\\Listeners\\NotifyAdminNewUser',   // Aşama 9
        // 'GO\\Events\\Listeners\\WriteActivityLog',
    ],

    'user.password_reset' => [
        // 'GO\\Events\\Listeners\\SendPasswordResetEmail',
    ],

    // ─── Proje eventleri ──────────────────────────────────────────────────────
    'project.created' => [
        // 'GO\\Events\\Listeners\\NotifyAdminNewProject',
        // 'GO\\Events\\Listeners\\WriteProjectTimeline',
        // 'GO\\Events\\Listeners\\UpdateProjectMemory',
    ],

    'project.deleted' => [
        // 'GO\\Events\\Listeners\\NotifyAdminProjectDeleted',
        // 'GO\\Events\\Listeners\\WriteProjectTimeline',
    ],

    'project.updated' => [
        // 'GO\\Events\\Listeners\\WriteProjectTimeline',
        // 'GO\\Events\\Listeners\\UpdateProjectMemory',
    ],

    // ─── Chat eventleri ───────────────────────────────────────────────────────
    'chat.completed' => [
        // 'GO\\Events\\Listeners\\WriteProjectTimeline',
        // 'GO\\Events\\Listeners\\NotifyAdminChatCompleted',
    ],

    // ─── Ödeme eventleri ──────────────────────────────────────────────────────
    'payment.reported' => [
        // 'GO\\Events\\Listeners\\NotifyAdminPaymentReported',
        // 'GO\\Events\\Listeners\\CreateInAppNotification',
        // 'GO\\Events\\Listeners\\WriteAuditTrail',
        // 'GO\\Events\\Listeners\\WriteProjectTimeline',
    ],

    'payment.approved' => [
        // 'GO\\Events\\Listeners\\NotifyUserPaymentApproved',
        // 'GO\\Events\\Listeners\\WriteAuditTrail',
        // 'GO\\Events\\Listeners\\WriteProjectTimeline',
    ],

    // ─── Domain talep eventleri ───────────────────────────────────────────────
    'domain_request.created' => [
        // 'GO\\Events\\Listeners\\NotifyAdminDomainRequest',
        // 'GO\\Events\\Listeners\\CreateInAppNotification',
        // 'GO\\Events\\Listeners\\WriteProjectTimeline',
    ],

    'domain_request.completed' => [
        // 'GO\\Events\\Listeners\\NotifyUserDomainCompleted',
        // 'GO\\Events\\Listeners\\WriteProjectTimeline',
    ],

    // ─── Destek ticket eventleri ──────────────────────────────────────────────
    'support.ticket_created' => [
        // 'GO\\Events\\Listeners\\NotifyAdminNewTicket',
        // 'GO\\Events\\Listeners\\CreateInAppNotification',
    ],

    'support.replied' => [
        // 'GO\\Events\\Listeners\\NotifyUserSupportReply',
        // 'GO\\Events\\Listeners\\CreateInAppNotification',
    ],

    // ─── Fatura eventleri ─────────────────────────────────────────────────────
    'invoice.created' => [
        // 'GO\\Events\\Listeners\\NotifyUserInvoiceCreated',
        // 'GO\\Events\\Listeners\\WriteProjectTimeline',
    ],
];
