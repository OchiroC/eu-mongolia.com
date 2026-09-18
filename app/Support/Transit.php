<?php

namespace App\Support;

/**
 * Франкфуртаар дамжин Европын бусад хот руу явах хүмүүст зориулсан чиглэлүүд.
 * Хугацаа нь ойролцоо утга; хуваарь, үнийг DB Navigator эсвэл агаарын компаниас шалгуулна.
 */
class Transit
{
    /**
     * mode: train = FRA-гийн холын галт тэрэгний буудлаас, flight = холбох нислэгээр.
     * to: чиглэлийн нөхцөлтэй нэр (Берлин рүү), gen: улсын харьяалахын тийн ялгал (Германы).
     * schengen: false бол очих улс өөрийн визтэй (Шенгенд ордоггүй).
     *
     * @var array<string, array<string, mixed>>
     */
    public const CITIES = [
        'berlin' => [
            'name' => 'Берлин', 'to' => 'Берлин рүү', 'gen' => 'Германы', 'local' => 'Berlin', 'country' => 'Герман', 'schengen' => true, 'mode' => 'train',
            'time' => '4 цаг орчим', 'how' => 'ICE галт тэргээр, ихэнхдээ шууд эсвэл нэг сольж явна.',
            'note' => 'Монгол Улсын ЭСЯ Берлинд байрладаг.',
        ],
        'munich' => [
            'name' => 'Мюнхен', 'to' => 'Мюнхен рүү', 'gen' => 'Германы', 'local' => 'München', 'country' => 'Герман', 'schengen' => true, 'mode' => 'train',
            'time' => '3.5 цаг орчим', 'how' => 'ICE галт тэргээр, шууд эсвэл Штутгарт, Нюрнбергт сольж явна.',
        ],
        'hamburg' => [
            'name' => 'Гамбург', 'to' => 'Гамбург руу', 'gen' => 'Германы', 'local' => 'Hamburg', 'country' => 'Герман', 'schengen' => true, 'mode' => 'train',
            'time' => '4 цаг орчим', 'how' => 'ICE галт тэргээр, шууд эсвэл нэг сольж явна.',
        ],
        'cologne' => [
            'name' => 'Кёльн', 'to' => 'Кёльн рүү', 'gen' => 'Германы', 'local' => 'Köln', 'country' => 'Герман', 'schengen' => true, 'mode' => 'train',
            'time' => '1 цаг орчим', 'how' => 'Хурдны шугамаар шууд ICE явдаг. Дюссельдорф руу ч мөн адил.',
        ],
        'stuttgart' => [
            'name' => 'Штутгарт', 'to' => 'Штутгарт руу', 'gen' => 'Германы', 'local' => 'Stuttgart', 'country' => 'Герман', 'schengen' => true, 'mode' => 'train',
            'time' => '1.5 цаг орчим', 'how' => 'ICE галт тэргээр шууд, эсвэл Мангеймд сольж явна.',
        ],
        'amsterdam' => [
            'name' => 'Амстердам', 'to' => 'Амстердам руу', 'gen' => 'Нидерландын', 'local' => 'Amsterdam', 'country' => 'Нидерланд', 'schengen' => true, 'mode' => 'train',
            'time' => '4 цаг орчим', 'how' => 'Нисэх буудлын холын галт тэрэгний буудлаас шууд ICE явдаг.',
        ],
        'brussels' => [
            'name' => 'Брюссель', 'to' => 'Брюссель рүү', 'gen' => 'Бельгийн', 'local' => 'Bruxelles', 'country' => 'Бельги', 'schengen' => true, 'mode' => 'train',
            'time' => '3 цаг орчим', 'how' => 'Нисэх буудлын холын галт тэрэгний буудлаас шууд ICE явдаг.',
        ],
        'paris' => [
            'name' => 'Парис', 'to' => 'Парис руу', 'gen' => 'Францын', 'local' => 'Paris', 'country' => 'Франц', 'schengen' => true, 'mode' => 'train',
            'time' => '4.5 цаг орчим', 'how' => 'Франкфуртын төв буудал эсвэл Мангеймаас ICE, TGV-гээр явна. Нислэгээр 1 цаг орчим.',
        ],
        'prague' => [
            'name' => 'Прага', 'to' => 'Прага руу', 'gen' => 'Чехийн', 'local' => 'Praha', 'country' => 'Чех', 'schengen' => true, 'mode' => 'flight',
            'time' => '1 цаг орчим', 'how' => 'Холбох нислэгээр. Хямд сонголт нь автобус, 7 цаг орчим явна.',
            'note' => 'Чехэд монголчууд олноороо амьдардаг.',
        ],
        'vienna' => [
            'name' => 'Вена', 'to' => 'Вена руу', 'gen' => 'Австрийн', 'local' => 'Wien', 'country' => 'Австри', 'schengen' => true, 'mode' => 'flight',
            'time' => '1.5 цаг орчим', 'how' => 'Холбох нислэгээр. Галт тэргээр 7 цаг орчим явна.',
        ],
        'warsaw' => [
            'name' => 'Варшав', 'to' => 'Варшав руу', 'gen' => 'Польшийн', 'local' => 'Warszawa', 'country' => 'Польш', 'schengen' => true, 'mode' => 'flight',
            'time' => '2 цаг орчим', 'how' => 'Холбох нислэгээр.',
        ],
        'london' => [
            'name' => 'Лондон', 'to' => 'Лондон руу', 'gen' => 'Их Британийн', 'local' => 'London', 'country' => 'Их Британи', 'schengen' => false, 'mode' => 'flight',
            'time' => '1.5 цаг орчим', 'how' => 'Холбох нислэгээр.',
            'note' => 'Их Британи Шенгенд ордоггүй тул Британийн виз тусдаа хэрэгтэй.',
        ],
    ];

    /** Зорчигчийн "цааш хаашаа" сонголтонд Франкфуртад үлдэх хувилбар. */
    public const STAY = 'frankfurt';

    /** @return array<int, array<string, mixed>> */
    public static function all(): array
    {
        return collect(self::CITIES)->map(fn ($c, $slug) => self::card($slug))->values()->all();
    }

    public static function exists(?string $slug): bool
    {
        return $slug !== null && isset(self::CITIES[$slug]);
    }

    /** @return array<string, mixed> */
    public static function card(string $slug): array
    {
        $c = self::CITIES[$slug];

        return [
            'slug' => $slug,
            'name' => $c['name'],
            'local' => $c['local'],
            'country' => $c['country'],
            'to' => $c['to'],
            'gen' => $c['gen'],
            'schengen' => $c['schengen'],
            'mode' => $c['mode'],
            'mode_label' => $c['mode'] === 'train' ? 'Галт тэрэг' : 'Нислэг',
            'time' => $c['time'],
            'how' => $c['how'],
            'note' => $c['note'] ?? null,
        ];
    }

    /** Зорчигчийн сонголтын жагсаалт: slug => нэр. */
    public static function options(): array
    {
        return [self::STAY => 'Франкфурт орчимд үлдэнэ']
            + collect(self::CITIES)->map(fn ($c) => $c['name'])->all();
    }

    public static function label(?string $slug): ?string
    {
        return $slug ? (self::options()[$slug] ?? null) : null;
    }
}
