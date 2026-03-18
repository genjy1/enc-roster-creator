<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCountryRosterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<string>> */
    public function rules(): array
    {
        return [
            'country_id' => ['required', 'integer', 'exists:countries,id'],
            'player_ids' => ['required', 'array', 'min:1'],
            'player_ids.*' => ['integer', 'exists:players,id', 'distinct'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'country_id.required' => 'Страна обязательна.',
            'country_id.exists' => 'Выбранная страна не найдена.',
            'player_ids.required' => 'Добавьте хотя бы одного игрока.',
            'player_ids.min' => 'Добавьте хотя бы одного игрока.',
            'player_ids.*.exists' => 'Один или несколько игроков не найдены.',
            'player_ids.*.distinct' => 'Игроки не должны повторяться.',
        ];
    }
}
