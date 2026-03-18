<?php

namespace App\Console\Commands;

use App\Models\Country;
use App\Models\Player;
use DOMDocument;
use DOMXPath;
use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

class ImportHltvPlayers extends Command
{
    protected $signature = 'import:hltv-players
                            {--api-key= : ScraperAPI key (get a free one at scraperapi.com)}
                            {--dry-run : Parse and display players without saving to the database}';

    protected $description = 'Import CS2 players from hltv.org/players into the database (requires --api-key for ScraperAPI)';

    private const BASE_URL = 'https://www.hltv.org/players';

    private const SCRAPER_API_URL = 'http://api.scraperapi.com';

    private const OFFSET_STEP = 52;

    private const OFFSET_MAX = 312;

    private const REQUEST_DELAY_MS = 2000;

    /** @var array<string, int> */
    private array $countryCache = [];

    public function handle(): int
    {
        $apiKey = $this->option('api-key') ?? config('services.scraperapi.key');
        $isDryRun = (bool) $this->option('dry-run');

        if (empty($apiKey)) {
            $this->error('A ScraperAPI key is required to bypass Cloudflare protection on HLTV.');
            $this->line('  1. Sign up for free at <href=https://scraperapi.com>scraperapi.com</> (1,000 free requests/month).');
            $this->line('  2. Run: php artisan import:hltv-players --api-key=YOUR_KEY');
            $this->line('  3. Or set SCRAPER_API_KEY in your .env file.');

            return self::FAILURE;
        }

        if ($isDryRun) {
            $this->warn('Dry-run mode — no data will be saved.');
        }

        $totalImported = 0;
        $totalSkipped = 0;

        for ($offset = 0; $offset <= self::OFFSET_MAX; $offset += self::OFFSET_STEP) {
            $page = ($offset / self::OFFSET_STEP) + 1;
            $totalPages = (self::OFFSET_MAX / self::OFFSET_STEP) + 1;

            $this->info("Fetching page {$page}/{$totalPages} (offset={$offset})…");

            try {
                $html = $this->fetchPage($offset, $apiKey);
            } catch (RequestException $e) {
                $status = $e->response->status();
                $this->error("  HTTP {$status} on page {$page}. Check your ScraperAPI key or account credits.");

                continue;
            } catch (ConnectionException $e) {
                $this->error("  Connection failed for offset={$offset}: {$e->getMessage()}");

                continue;
            }

            $players = $this->parsePlayers($html);

            if (empty($players)) {
                $this->warn("  No players parsed on page {$page} — HTML structure may have changed.");

                continue;
            }

            $this->info('  Found '.count($players).' players.');

            foreach ($players as $playerData) {
                if ($isDryRun) {
                    $this->line(sprintf(
                        '  [dry-run] %s (%s %s) — %s, %s',
                        $playerData['nickname'],
                        $playerData['name'],
                        $playerData['surname'],
                        $playerData['country_name'],
                        $playerData['primary_position'],
                    ));
                    $totalImported++;

                    continue;
                }

                $countryId = $this->resolveCountryId(
                    $playerData['country_name'],
                    $playerData['country_code'],
                );

                if ($countryId === null) {
                    $totalSkipped++;

                    continue;
                }

                $player = Player::updateOrCreate(
                    ['nickname' => $playerData['nickname']],
                    [
                        'name' => $playerData['name'],
                        'surname' => $playerData['surname'],
                        'date_of_birth' => $playerData['date_of_birth'],
                        'position' => $playerData['primary_position'],
                        'country_id' => $countryId,
                        'photo_url' => $playerData['photo_url'],
                    ],
                );

                $player->wasRecentlyCreated ? $totalImported++ : $totalSkipped++;
            }

            if ($offset < self::OFFSET_MAX) {
                usleep(self::REQUEST_DELAY_MS * 1000);
            }
        }

        $this->newLine();

        if ($isDryRun) {
            $this->info("Dry-run complete. Would import {$totalImported} player(s).");
        } else {
            $this->info("Import complete. Imported: {$totalImported}, skipped (already exist): {$totalSkipped}.");
        }

        return self::SUCCESS;
    }

    private function fetchPage(int $offset, string $apiKey): string
    {
        $targetUrl = self::BASE_URL.'?offset='.$offset;

        $response = Http::timeout(60)
            ->get(self::SCRAPER_API_URL, [
                'api_key' => $apiKey,
                'url' => $targetUrl,
            ]);

        $response->throw();

        return $response->body();
    }

    /**
     * @return list<array{nickname: string, name: string, surname: string, date_of_birth: string, position: string, country_name: string, country_code: string, photo_url: string|null}>
     */
    private function parsePlayers(string $html): array
    {
        $document = new DOMDocument;

        libxml_use_internal_errors(true);
        $document->loadHTML($html, LIBXML_NOWARNING | LIBXML_NOERROR);
        libxml_clear_errors();

        $xpath = new DOMXPath($document);
        $players = [];

        // Each player is an <a class="players-archive-box"> inside .players-archive-grid
        $cards = $xpath->query('//*[contains(@class,"players-archive-box")]');

        if ($cards === false || $cards->length === 0) {
            return [];
        }

        foreach ($cards as $card) {
            $nickname = $this->xpathText($xpath, './/*[contains(@class,"players-archive-nickname")]', $card);

            if ($nickname === null || $nickname === '') {
                continue;
            }

            $fullName = $this->xpathText($xpath, './/*[contains(@class,"players-archive-name")]', $card) ?? '';
            [$name, $surname] = $this->splitFullName($fullName);

            $countryName = '';
            $countryCode = '';

            $flagImg = $xpath->query('.//*[contains(@class,"players-archive-country")]//img[contains(@class,"flag")]', $card);
            if ($flagImg !== false && $flagImg->length > 0) {
                $flag = $flagImg->item(0);
                $countryName = $flag->getAttribute('title');
                $src = $flag->getAttribute('src');
                // src: /img/static/flags/30x20/BY.gif
                if (preg_match('#/([A-Z]{2})\.(?:gif|png|svg|webp)#i', $src, $m)) {
                    $countryCode = strtoupper($m[1]);
                }
            }

            // Photo: <img class="players-archive-bodyshot" src="https://img-cdn.hltv.org/...">
            $photoUrl = null;
            $photoImg = $xpath->query('.//*[contains(@class,"players-archive-bodyshot")]', $card);
            if ($photoImg !== false && $photoImg->length > 0) {
                $src = $photoImg->item(0)->getAttribute('src');
                $photoUrl = $src !== '' ? $src : null;
            }

            // Position and age are not shown on the list page — use defaults
            $players[] = [
                'nickname' => trim($nickname),
                'name' => $name,
                'surname' => $surname,
                'date_of_birth' => now()->subYears(22)->format('Y-m-d'),
                'primary_position' => 'Rifler',
                'country_name' => trim($countryName),
                'country_code' => $countryCode,
                'photo_url' => $photoUrl,
            ];
        }

        return $players;
    }

    /**
     * @param  \DOMNode  $context
     */
    private function xpathText(DOMXPath $xpath, string $query, mixed $context): ?string
    {
        $nodes = $xpath->query($query, $context);

        if ($nodes === false || $nodes->length === 0) {
            return null;
        }

        $text = trim($nodes->item(0)->textContent);

        return $text !== '' ? $text : null;
    }

    /** @return array{string, string} */
    private function splitFullName(string $fullName): array
    {
        $parts = explode(' ', trim($fullName), 2);

        return [
            $parts[0] ?? '',
            $parts[1] ?? '',
        ];
    }

    private function resolveCountryId(string $countryName, string $countryCode): ?int
    {
        $cacheKey = $countryCode ?: $countryName;

        if (isset($this->countryCache[$cacheKey])) {
            return $this->countryCache[$cacheKey];
        }

        if ($countryCode !== '') {
            $country = Country::query()->firstOrCreate(
                ['code' => $countryCode],
                ['name' => $countryName ?: $countryCode],
            );

            return $this->countryCache[$cacheKey] = $country->id;
        }

        if ($countryName !== '') {
            $country = Country::query()->firstOrCreate(
                ['name' => $countryName],
                ['code' => strtoupper(substr($countryName, 0, 2))],
            );

            return $this->countryCache[$cacheKey] = $country->id;
        }

        $this->warn('  Could not resolve country for a player, skipping.');

        return null;
    }
}
