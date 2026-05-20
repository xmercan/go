<?php

namespace GO\Jobs;

/**
 * Tüm job sınıflarının temel sınıfı.
 * cli/worker.php tarafından çağrılır.
 */
abstract class BaseJob
{
    /**
     * Job'u çalıştır.
     *
     * @param array $payload  QueueService::push ile eklenen veri
     * @throws \Throwable     Hata olursa worker yakalar ve fail() çağırır
     */
    abstract public function handle(array $payload): void;

    /**
     * Hangi queue adında çalışır.
     */
    abstract public function queueName(): string;
}
