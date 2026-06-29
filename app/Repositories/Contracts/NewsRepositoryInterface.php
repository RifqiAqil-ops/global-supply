<?php

namespace App\Repositories\Contracts;

use App\Interfaces\RepositoryInterface;
use App\Models\NewsArticle;
use Illuminate\Database\Eloquent\Collection;

interface NewsRepositoryInterface extends RepositoryInterface
{
    /**
     * Get the latest news articles, optionally filtered by category.
     *
     * @param int $limit
     * @param string|null $category
     * @return Collection
     */
    public function latestArticles(int $limit = 20, ?string $category = null): Collection;

    /**
     * Get news articles for a specific country.
     *
     * @param int $countryId
     * @param int $limit
     * @param string|null $category
     * @return Collection
     */
    public function articlesByCountry(int $countryId, int $limit = 10, ?string $category = null): Collection;

    /**
     * Get news articles by category.
     *
     * @param string $category
     * @param int $limit
     * @return Collection
     */
    public function articlesByCategory(string $category, int $limit = 20): Collection;

    /**
     * Search news articles by keyword.
     *
     * @param string $query
     * @param int $limit
     * @return Collection
     */
    public function search(string $query, int $limit = 20): Collection;

    /**
     * Check if the source URL already exists to prevent duplicates.
     *
     * @param string $sourceUrl
     * @return bool
     */
    public function existsByUrl(string $sourceUrl): bool;
}
