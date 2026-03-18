<?php

namespace App\Http\Requests;

use App\Models\Player;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePlayerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $playerId = $this->route('player') instanceof Player
            ? $this->route('player')->id
            : $this->route('player');

        return [
            'nickname'      => ['required', 'string', 'max:64', Rule::unique('players', 'nickname')->ignore($playerId)],
            'name'          => ['required', 'string', 'max:128'],
            'surname'       => ['required', 'string', 'max:128'],
            'date_of_birth' => ['required', 'date'],
            'primary_position'   => ['required', 'string', Rule::in(['AWPer', 'IGL', 'Rifler', 'Support', 'Lurker', 'Entry Fragger'])],
            'secondary_position' => ['nullable', 'string', Rule::in(['AWPer', 'IGL', 'Rifler', 'Support', 'Lurker', 'Entry Fragger']), 'different:primary_position'],
            'country_id'         => ['required', 'integer', 'exists:countries,id'],
            'photo_url'          => ['nullable', 'url', 'max:512'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nickname.required'      => 'Никнейм обязателен.',
            'nickname.unique'        => 'Этот никнейм уже занят.',
            'name.required'          => 'Имя обязательно.',
            'surname.required'       => 'Фамилия обязательна.',
            'date_of_birth.required' => 'Дата рождения обязательна.',
            'date_of_birth.date'     => 'Некорректная дата.',
            'primary_position.required'      => 'Основная позиция обязательна.',
            'primary_position.in'            => 'Неверная основная позиция.',
            'secondary_position.in'          => 'Неверная второстепенная позиция.',
            'secondary_position.different'   => 'Второстепенная позиция не должна совпадать с основной.',
            'country_id.required'    => 'Страна обязательна.',
            'country_id.exists'      => 'Страна не найдена.',
            'photo_url.url'          => 'Некорректный URL фото.',
        ];
    }
}
