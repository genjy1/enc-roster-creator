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
            'position'      => ['required', 'string', Rule::in(['AWPer', 'IGL', 'Rifler', 'Support', 'Lurker', 'Entry Fragger'])],
            'country_id'    => ['required', 'integer', 'exists:countries,id'],
            'photo_url'     => ['nullable', 'url', 'max:512'],
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
            'position.required'      => 'Позиция обязательна.',
            'position.in'            => 'Неверная позиция.',
            'country_id.required'    => 'Страна обязательна.',
            'country_id.exists'      => 'Страна не найдена.',
            'photo_url.url'          => 'Некорректный URL фото.',
        ];
    }
}
