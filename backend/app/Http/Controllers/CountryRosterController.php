<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCountryRosterRequest;
use App\Models\CountryRoster;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CountryRosterController extends Controller
{
    /**
     * Return the current roster for a country.
     */
    public function index(int $countryId): JsonResponse
    {
        $roster = CountryRoster::with('player')
            ->where('country_id', $countryId)
            ->get()
            ->pluck('player');

        return response()->json(['data' => $roster]);
    }

    /**
     * Return all rosters grouped by country.
     */
    public function list(): JsonResponse
    {
        $rosters = CountryRoster::with(['country', 'player'])
            ->get()
            ->groupBy('country_id')
            ->map(fn ($entries) => [
                'id' => $entries->first()->country->id,
                'name' => $entries->first()->country->name,
                'code' => $entries->first()->country->code,
                'players' => $entries->pluck('player')->values(),
            ])
            ->values();

        return response()->json(['data' => $rosters]);
    }

    /**
     * Replace the roster for a country with the submitted player list.
     * Wraps everything in a transaction so a partial failure leaves no orphans.
     */
    public function store(StoreCountryRosterRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $roster = DB::transaction(function () use ($validated): Collection {
            CountryRoster::where('country_id', $validated['country_id'])->delete();

            $now = now();

            CountryRoster::insert(
                array_map(
                    fn (int $playerId) => [
                        'country_id' => $validated['country_id'],
                        'player_id' => $playerId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ],
                    $validated['player_ids'],
                ),
            );

            return CountryRoster::with('player')
                ->where('country_id', $validated['country_id'])
                ->get()
                ->pluck('player');
        });

        return response()->json(['data' => $roster], 201);
    }

    /**
     * Remove a single player from a country's roster.
     */
    public function destroy(int $countryId, int $playerId): JsonResponse
    {
        CountryRoster::where('country_id', $countryId)
            ->where('player_id', $playerId)
            ->delete();

        return response()->json(['data' => true]);
    }
}
