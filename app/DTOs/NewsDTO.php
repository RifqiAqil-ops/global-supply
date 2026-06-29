<?php

namespace App\DTOs;

class NewsDTO
{
    public bool $isCached = false;

    public function __construct(
        public readonly string $title,
        public readonly ?string $description = null,
        public readonly ?string $content = null,
        public readonly ?string $sourceName = null,
        public readonly string $sourceUrl = '',
        public readonly ?string $imageUrl = null,
        public readonly string $category = 'general',
        public readonly string $sentiment = 'neutral',
        public readonly ?string $publishedAt = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            title: $data['title'] ?? '',
            description: $data['description'] ?? null,
            content: $data['content'] ?? null,
            sourceName: $data['source_name'] ?? null,
            sourceUrl: $data['source_url'] ?? '',
            imageUrl: $data['image_url'] ?? null,
            category: $data['category'] ?? 'general',
            sentiment: $data['sentiment'] ?? 'neutral',
            publishedAt: $data['published_at'] ?? null,
        );
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
