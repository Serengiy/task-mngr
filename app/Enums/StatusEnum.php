<?php

namespace App\Enums;

enum StatusEnum: int
{
    case NEW = 0;
    case IN_PROGRESS = 1;
    case DONE = 2;
    case CANCELED = 3;

    public function label(): string
    {
        return match ($this) {
            self::NEW => 'Новая',
            self::IN_PROGRESS => 'В работе',
            self::DONE => 'Завершена',
            self::CANCELED => 'Отменена',
        };
    }

    public static function labels(): array
    {
        return array_map(function ($label) {
            return $label->label();
        }, self::cases());
    }
}
