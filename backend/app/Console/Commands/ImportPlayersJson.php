<?php

namespace App\Console\Commands;

use App\Models\Country;
use App\Models\Player;
use Illuminate\Console\Command;

class ImportPlayersJson extends Command
{
    protected $signature = 'import:players-json
                            {--file=players.json : Path relative to storage/app (not storage/app/private)}
                            {--dry-run : Show what would be imported without saving}';

    protected $description = 'Import players from a JSON file produced by the Puppeteer scraper';

    /** @var array<string, int> */
    private array $countryCache = [];

    public function handle(): int
    {
        $file = $this->option('file');
        $isDryRun = (bool) $this->option('dry-run');

        $path = storage_path('app/'.ltrim($file, '/'));

        if (! file_exists($path)) {
            $this->error("File not found: {$path}");
            $this->line('Run the scraper first: docker compose --profile scraper run --rm scraper');

            return self::FAILURE;
        }

        $json = file_get_contents($path);
        $players = json_decode($json, true);

        if (! is_array($players) || empty($players)) {
            $this->error('JSON file is empty or invalid.');

            return self::FAILURE;
        }

        $this->info('Found '.count($players).' players in file.');

        if ($isDryRun) {
            $this->warn('Dry-run mode — nothing will be saved.');
        }

        $imported = 0;
        $skipped = 0;

        foreach ($players as $data) {
            $nickname = trim($data['nickname'] ?? '');
            if ($nickname === '') {
                continue;
            }

            if ($isDryRun) {
                $this->line(sprintf(
                    '  %s (%s %s) — %s [%s]%s',
                    $nickname,
                    $data['name'] ?? '',
                    $data['surname'] ?? '',
                    $data['countryName'] ?? '',
                    $data['countryCode'] ?? '',
                    isset($data['photoUrl']) ? ' 📷' : '',
                ));
                $imported++;

                continue;
            }

            $countryId = $this->resolveCountryId(
                $data['countryName'] ?? '',
                $data['countryCode'] ?? '',
            );

            if ($countryId === null) {
                $skipped++;

                continue;
            }

            $player = Player::updateOrCreate(
                ['nickname' => $nickname],
                [
                    'name' => $data['name'] ?? '',
                    'surname' => $data['surname'] ?? '',
                    'date_of_birth' => now()->subYears(22)->format('Y-m-d'),
                    'position' => 'Rifler',
                    'country_id' => $countryId,
                    'photo_url' => $data['photoUrl'] ?? null,
                ],
            );

            $player->wasRecentlyCreated ? $imported++ : $skipped++;
        }

        $this->newLine();

        if ($isDryRun) {
            $this->info("Dry-run complete. Would import {$imported} player(s).");
        } else {
            $this->info("Import complete. Imported: {$imported}, skipped (already exist): {$skipped}.");
        }

        return self::SUCCESS;
    }

    private function resolveCountryId(string $countryName, string $countryCode): ?int
    {
        $cacheKey = $countryCode ?: $countryName;

        if (isset($this->countryCache[$cacheKey])) {
            return $this->countryCache[$cacheKey];
        }

        if ($countryCode !== '') {
            $country = Country::firstOrCreate(
                ['code' => $countryCode],
                ['name' => $countryName ?: $countryCode],
            );

            return $this->countryCache[$cacheKey] = $country->id;
        }

        if ($countryName !== '') {
            $country = Country::firstOrCreate(
                ['name' => $countryName],
                ['code' => strtoupper(substr($countryName, 0, 2))],
            );

            return $this->countryCache[$cacheKey] = $country->id;
        }

        return null;
    }
}
