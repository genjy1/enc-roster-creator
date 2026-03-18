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
use Illuminate\Support\Facades\Storage;

class ImportHltvPlayers extends Command
{
    protected $signature = 'import:hltv-players
                            {--api-key=   : ScraperAPI key}
                            {--from=      : Resume from a specific letter, e.g. --from=G}
                            {--only=      : Process only one letter, e.g. --only=S}
                            {--reset      : Clear saved progress and start from scratch}
                            {--dry-run    : Parse and display players without saving}';

    protected $description = 'Import CS2 players from hltv.org/players (by letter A–Z, resumable)';

    private const BASE_URL = 'https://www.hltv.org/players';

    private const SCRAPER_API_URL = 'http://api.scraperapi.com';

    private const REQUEST_DELAY_MS = 2500;

    private const PROGRESS_FILE = 'hltv_import_progress.json';

    /** All letter buckets HLTV supports */
    private const LETTERS = [
        '#', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I',
        'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S',
        'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
    ];

    /** @var array<string, int> */
    private array $countryCache = [];

    public function handle(): int
    {
        $apiKey = $this->option('api-key') ?? config('services.scraperapi.key');
        $isDryRun = (bool) $this->option('dry-run');
        $onlyLetter = $this->option('only') ? strtoupper((string) $this->option('only')) : null;
        $fromLetter = $this->option('from') ? strtoupper((string) $this->option('from')) : null;

        if (empty($apiKey)) {
            $this->error('ScraperAPI key is required.');
            $this->line('  Set SCRAPER_API_KEY in .env or pass --api-key=YOUR_KEY');

            return self::FAILURE;
        }

        if ($this->option('reset')) {
            Storage::delete(self::PROGRESS_FILE);
            $this->info('Progress reset.');
        }

        $progress = $this->loadProgress();

        // Determine which letters to process
        $letters = $onlyLetter
            ? [$onlyLetter]
            : self::LETTERS;

        if ($fromLetter !== null && ! $onlyLetter) {
            $idx = array_search($fromLetter, $letters, true);
            if ($idx !== false) {
                $letters = array_slice($letters, (int) $idx);
            }
        }

        // Skip already-completed letters unless --only or --reset was used
        if (! $onlyLetter && ! $this->option('reset')) {
            $letters = array_values(array_filter(
                $letters,
                fn (string $l) => ! in_array($l, $progress['done'], true),
            ));
        }

        if (empty($letters)) {
            $this->info('All letters already imported. Use --reset to start over.');

            return self::SUCCESS;
        }

        if ($isDryRun) {
            $this->warn('Dry-run mode — no data will be saved.');
        }

        $this->info(sprintf(
            'Letters to process: %s',
            implode(' ', $letters),
        ));

        $this->newLine();

        $totalImported = 0;
        $totalLetters = count($letters);

        foreach ($letters as $idx => $letter) {
            $num = $idx + 1;
            $this->info("[{$num}/{$totalLetters}] Letter «{$letter}»…");

            try {
                $html = $this->fetchLetter($letter, $apiKey);
            } catch (RequestException $e) {
                $status = $e->response->status();
                $this->error("  HTTP {$status} for letter «{$letter}». Skipping.");

                continue;
            } catch (ConnectionException $e) {
                $this->error("  Connection error for «{$letter}»: {$e->getMessage()}");

                continue;
            }

            $players = $this->parsePlayers($html);

            if (empty($players)) {
                $this->warn("  No players found for «{$letter}» — page structure may have changed.");
                $this->markDone($letter);

                continue;
            }

            $this->line('  Found '.count($players).' player(s).');

            foreach ($players as $playerData) {
                if ($isDryRun) {
                    $this->line(sprintf(
                        '  [dry] %s (%s %s) — %s [%s]',
                        $playerData['nickname'],
                        $playerData['name'],
                        $playerData['surname'],
                        $playerData['country_name'],
                        $playerData['country_code'],
                    ));
                    $totalImported++;

                    continue;
                }

                $countryId = $this->resolveCountryId(
                    $playerData['country_name'],
                    $playerData['country_code'],
                );

                if ($countryId === null) {
                    continue;
                }

                Player::create([
                    'nickname' => $playerData['nickname'],
                    'name' => $playerData['name'],
                    'surname' => $playerData['surname'],
                    'date_of_birth' => $playerData['date_of_birth'],
                    'primary_position' => $playerData['primary_position'],
                    'country_id' => $countryId,
                    'photo_url' => $playerData['photo_url'],
                ]);

                $totalImported++;
            }

            if (! $isDryRun) {
                $this->markDone($letter);
                $this->line("  ✓ «{$letter}» saved to progress.");
            }

            if ($idx < $totalLetters - 1) {
                usleep(self::REQUEST_DELAY_MS * 1000);
            }
        }

        $this->newLine();

        if ($isDryRun) {
            $this->info("Dry-run done. Would import ~{$totalImported} player(s).");
        } else {
            $this->info("Done. Imported: {$totalImported} player(s).");

            $remaining = array_diff(self::LETTERS, $this->loadProgress()['done']);
            if (! empty($remaining)) {
                $this->line('Remaining letters: '.implode(' ', $remaining));
                $this->line('Resume with: php artisan import:hltv-players --api-key=KEY --from='.reset($remaining));
            } else {
                $this->info('All letters complete!');
            }
        }

        return self::SUCCESS;
    }

    private function fetchLetter(string $letter, string $apiKey): string
    {
        $targetUrl = self::BASE_URL.'/'.$letter;

        $response = Http::timeout(60)
            ->get(self::SCRAPER_API_URL, [
                'api_key' => $apiKey,
                'url' => $targetUrl,
            ]);

        $response->throw();

        return $response->body();
    }

    /**
     * @return list<array{nickname: string, name: string, surname: string, date_of_birth: string, primary_position: string, country_name: string, country_code: string, photo_url: string|null}>
     */
    private function parsePlayers(string $html): array
    {
        $document = new DOMDocument;

        libxml_use_internal_errors(true);
        $document->loadHTML($html, LIBXML_NOWARNING | LIBXML_NOERROR);
        libxml_clear_errors();

        $xpath = new DOMXPath($document);
        $players = [];

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
                if (preg_match('#/([A-Z]{2})\.(?:gif|png|svg|webp)#i', $src, $m)) {
                    $countryCode = strtoupper($m[1]);
                }
            }

            $photoUrl = null;
            $photoImg = $xpath->query('.//*[contains(@class,"players-archive-bodyshot")]', $card);
            if ($photoImg !== false && $photoImg->length > 0) {
                $src = $photoImg->item(0)->getAttribute('src');
                $photoUrl = $src !== '' ? $src : null;
            }

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

    // ─── Progress ─────────────────────────────────────────────────────────────

    /** @return array{done: list<string>} */
    private function loadProgress(): array
    {
        if (! Storage::exists(self::PROGRESS_FILE)) {
            return ['done' => []];
        }

        $data = json_decode(Storage::get(self::PROGRESS_FILE) ?? '{}', true);

        return is_array($data) ? $data : ['done' => []];
    }

    private function markDone(string $letter): void
    {
        $progress = $this->loadProgress();

        if (! in_array($letter, $progress['done'], true)) {
            $progress['done'][] = $letter;
        }

        Storage::put(self::PROGRESS_FILE, json_encode($progress, JSON_PRETTY_PRINT));
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

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

        return [$parts[0] ?? '', $parts[1] ?? ''];
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

        $this->warn('  Could not resolve country, skipping player.');

        return null;
    }
}
