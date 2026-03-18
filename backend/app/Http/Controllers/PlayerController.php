<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdatePlayerRequest;
use App\Models\Player;
use Illuminate\Http\JsonResponse;

class PlayerController extends Controller
{
    public function index(): JsonResponse
    {
        $players = Player::query()
            ->with('country')
            ->orderBy('nickname')
            ->get()
            ->map(fn (Player $player) => $this->formatPlayer($player));

        return response()->json(['data' => $players]);
    }

    public function show(Player $player): JsonResponse
    {
        $player->load('country');

        return response()->json(['data' => $this->formatPlayer($player)]);
    }

    public function update(UpdatePlayerRequest $request, Player $player): JsonResponse
    {
        $player->update($request->validated());
        $player->load('country');

        return response()->json(['data' => $this->formatPlayer($player)]);
    }

    /**
     * @return array<string, mixed>
     */
    private function formatPlayer(Player $player): array
    {
        return [
            'id'           => $player->id,
            'nickname'     => $player->nickname,
            'name'         => $player->name,
            'surname'      => $player->surname,
            'date_of_birth' => $player->date_of_birth,
            'position'     => $player->position,
            'photo_url'    => $player->photo_url,
            'country_id'   => $player->country_id,
            'country_code' => $player->country?->code,
            'country_name' => $player->country?->name,
        ];
    }
}
