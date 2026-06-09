<?php

namespace Modules\Shared\Services;

use DOMDocument;
use DOMElement;
use DOMXPath;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class MinistryNewsService
{
    private const SOURCE_URL = 'https://tve.gov.ly/Home/Index3';

    private const BASE_URL = 'https://tve.gov.ly';

    /**
     * @return array<int, array{title: string, link: string, author: ?string, published_at: ?string, image_url: ?string}>
     */
    public function fetch(): array
    {
        try {
            $response = Http::accept('text/html')
                ->connectTimeout(5)
                ->timeout(15)
                ->retry(1, 500)
                ->get(self::SOURCE_URL);

            if (! $response->successful()) {
                Log::warning('Ministry news fetch failed.', [
                    'status' => $response->status(),
                    'source' => self::SOURCE_URL,
                ]);

                return [];
            }

            return $this->parse($response->body());
        } catch (Throwable $exception) {
            Log::warning('Ministry news fetch failed.', [
                'source' => self::SOURCE_URL,
                'error' => $exception->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * @return array<int, array{title: string, link: string, author: ?string, published_at: ?string, image_url: ?string}>
     */
    private function parse(string $html): array
    {
        if (trim($html) === '') {
            return [];
        }

        $previous = libxml_use_internal_errors(true);
        $document = new DOMDocument;
        $document->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_NOERROR | LIBXML_NOWARNING);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $xpath = new DOMXPath($document);
        $articles = $xpath->query('//article[.//a[contains(@href, "/News/Details/")]]');
        $items = [];
        $seen = [];

        foreach ($articles ?: [] as $article) {
            if (! $article instanceof DOMElement) {
                continue;
            }

            $link = $this->firstElement($xpath->query('.//h2[contains(concat(" ", normalize-space(@class), " "), " title ")]//a[contains(@href, "/News/Details/")]', $article))
                ?? $this->firstElement($xpath->query('.//a[contains(@href, "/News/Details/")]', $article));

            if (! $link instanceof DOMElement) {
                continue;
            }

            $url = $this->absoluteUrl($link->getAttribute('href'));
            $title = $this->cleanText($link->textContent);

            if ($title === '' || $url === '' || isset($seen[$url])) {
                continue;
            }

            $image = $this->firstElement($xpath->query('.//img', $article));
            $author = $this->firstElement($xpath->query('.//*[contains(concat(" ", normalize-space(@class), " "), " author-info ")]//p', $article));
            $date = $this->firstElement($xpath->query('.//*[contains(concat(" ", normalize-space(@class), " "), " post-info ")]//span[contains(., "/")]', $article));

            $items[] = [
                'title' => $title,
                'link' => $url,
                'author' => $author instanceof DOMElement ? $this->cleanText($author->textContent) : null,
                'published_at' => $date instanceof DOMElement ? $this->extractDate($date->textContent) : $this->extractDate($article->textContent),
                'image_url' => $image instanceof DOMElement ? $this->absoluteUrl($image->getAttribute('src')) : null,
            ];

            $seen[$url] = true;
        }

        return $items;
    }

    private function firstElement(mixed $nodes): ?DOMElement
    {
        if (! $nodes || $nodes->length === 0) {
            return null;
        }

        $node = $nodes->item(0);

        return $node instanceof DOMElement ? $node : null;
    }

    private function cleanText(?string $value): string
    {
        $text = html_entity_decode((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/u', ' ', $text) ?? '';

        return trim($text);
    }

    private function extractDate(?string $value): ?string
    {
        $text = $this->cleanText($value);

        if (preg_match('/\b\d{1,2}\/\d{1,2}\/\d{4}\b/u', $text, $matches)) {
            return $matches[0];
        }

        return null;
    }

    private function absoluteUrl(?string $url): ?string
    {
        $url = trim((string) $url);

        if ($url === '') {
            return null;
        }

        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }

        if (str_starts_with($url, '//')) {
            return 'https:' . $url;
        }

        return self::BASE_URL . '/' . ltrim($url, '/');
    }
}
