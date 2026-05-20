<?php

namespace GO\Events;

/**
 * Basit event dispatcher (composer'sız).
 * Event → Listener mapping: events/listeners.php
 * V2: async listener → job queue delegate.
 */
class EventDispatcher
{
    private static array $listeners = [];
    private static bool  $booted    = false;

    /**
     * Event dinleyicilerini yükle (listeners.php).
     */
    public static function boot(): void
    {
        if (self::$booted) {
            return;
        }

        $listenersFile = GO_ROOT . '/events/listeners.php';
        if (file_exists($listenersFile)) {
            $map = require $listenersFile;
            if (is_array($map)) {
                self::$listeners = $map;
            }
        }

        self::$booted = true;
    }

    /**
     * Event fırlat.
     *
     * @param string $event    'project.created', 'payment.reported', vb.
     * @param array  $payload  Event verisi
     */
    public static function dispatch(string $event, array $payload = []): void
    {
        self::boot();

        $listeners = self::$listeners[$event] ?? [];

        foreach ($listeners as $listenerClass) {
            try {
                if (!class_exists($listenerClass)) {
                    continue;
                }

                $listener = new $listenerClass();

                if (method_exists($listener, 'handle')) {
                    $listener->handle($payload);
                }
            } catch (\Throwable $e) {
                // Listener hatası uygulamayı durdurmamalı
                self::logError($event, $listenerClass, $e);
            }
        }
    }

    /**
     * Runtime listener ekle.
     */
    public static function listen(string $event, string $listenerClass): void
    {
        self::$listeners[$event][] = $listenerClass;
    }

    /**
     * Kayıtlı event'leri listele (debug için).
     */
    public static function registered(): array
    {
        self::boot();
        return array_keys(self::$listeners);
    }

    // ─── Private ──────────────────────────────────────────────────────────────

    private static function logError(string $event, string $listener, \Throwable $e): void
    {
        $logPath = defined('LOG_PATH') ? LOG_PATH : GO_ROOT . '/storage/logs';
        $msg = sprintf(
            '[%s] EVENT_ERROR event=%s listener=%s error=%s' . PHP_EOL,
            date('Y-m-d H:i:s'),
            $event,
            $listener,
            $e->getMessage()
        );
        @file_put_contents($logPath . '/events.log', $msg, FILE_APPEND | LOCK_EX);
    }
}
