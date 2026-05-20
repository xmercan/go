<?php

namespace GO\Integrations\Ai;

/**
 * AI provider arayüzü.
 * V1: NullAiProvider (state machine). V2: OpenAiProvider, yerli model.
 */
interface AiProviderInterface
{
    /**
     * Kullanıcı mesajına yanıt üret.
     *
     * @param string $message  Kullanıcı mesajı
     * @param array  $context  Proje bağlamı (ai_context JSON)
     * @param array  $history  Son N mesaj history
     * @return string          GO! yanıtı
     */
    public function generateResponse(string $message, array $context = [], array $history = []): string;

    /**
     * Proje için AI özet üret.
     *
     * @param array $projectData  Proje + cevaplar
     * @return string             Özet metin
     */
    public function generateSummary(array $projectData): string;

    /**
     * Provider aktif mi?
     */
    public function isAvailable(): bool;

    /**
     * Provider adı.
     */
    public function getName(): string;
}
