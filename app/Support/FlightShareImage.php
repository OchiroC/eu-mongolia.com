<?php

namespace App\Support;

use App\Models\Flight;
use App\Models\FlightSchedule;
use GdImage;

/**
 * Нислэг бүрийн хуваалцах зураг (1200x630, Open Graph). Сайтын самбартай ижил
 * загвартай: хар дэвсгэр, split-flap хавтан, шар үсэг.
 */
class FlightShareImage
{
    private const W = 1200;

    private const H = 630;

    private const PAD = 64;

    private GdImage $img;

    private string $mono;

    private string $sans;

    public function __construct()
    {
        $this->mono = resource_path('fonts/IBMPlexMono-SemiBold.ttf');
        $this->sans = resource_path('fonts/IBMPlexSans-SemiBold.ttf');
    }

    /** Тоо, төлөв өөрчлөгдөхөд URL өөрчлөгдөж, Facebook шинэ зургийг татна. */
    public static function url(Flight $flight): string
    {
        return route('flights.share', $flight).'?v='.substr(md5(json_encode(self::stats($flight))), 0, 8);
    }

    public static function render(Flight $flight): string
    {
        return (new self)->draw($flight);
    }

    /** @return array<string, mixed> */
    private static function stats(Flight $flight): array
    {
        return [
            'people' => $flight->passengers()->count(),
            'rides' => $flight->rides()->active()->count(),
            'parcels' => $flight->parcels()->active()->count(),
            'status' => $flight->status,
            'at' => $flight->scheduled_at->timestamp,
        ];
    }

    private function draw(Flight $flight): string
    {
        $this->img = imagecreatetruecolor(self::W, self::H);
        imagealphablending($this->img, true);
        imagefilledrectangle($this->img, 0, 0, self::W, self::H, $this->rgb('0b0c0e'));

        $arrival = $flight->direction === 'arrival';
        $cancelled = $flight->status === 'cancelled';
        $local = $flight->localTime();
        $stats = self::stats($flight);
        $x = self::PAD;
        $col2 = 560;

        // Толгой: лого, чиглэл.
        $lx = $x;
        foreach (str_split('OM137') as $i => $ch) {
            $this->tile($lx, 56, 34, 48, $ch, 21, $i < 2 ? 'ffb81c' : 'ffffff', ['34363a', '28292d']);
            $lx += 37;
        }
        $head = ($arrival ? 'ИРЭХ / ANKUNFT' : 'ЯВАХ / ABFLUG').($cancelled ? '  ·  ЦУЦЛАГДСАН' : ($flight->status === 'delayed' ? '  ·  ХОЙШИЛСОН' : ''));
        $this->textRight(self::W - self::PAD, 88, $head, $this->mono, 15, $cancelled ? 'f87171' : 'ffffff', $cancelled ? 0 : 55);
        $this->line(136);

        // Том мөр: нислэгийн дугаар, цаг.
        $this->text($x, 186, 'НИСЛЭГ', $this->mono, 13, 'ffffff', 80);
        $this->text($col2, 186, ($arrival ? 'БУУХ ЦАГ' : 'ХӨӨРӨХ ЦАГ').' · ФРАНКФУРТ', $this->mono, 13, 'ffffff', 80);
        $this->tiles($x, 204, $flight->code, 70, 100, 50);
        $this->tiles($col2, 204, $local->format('H:i'), 70, 100, 50, $cancelled ? 90 : 0);

        // Дунд мөр: чиглэл, огноо.
        $weekday = FlightSchedule::WEEKDAYS[$local->isoWeekday()] ?? '';
        $this->text($x, 362, 'ЧИГЛЭЛ', $this->mono, 13, 'ffffff', 80);
        $this->text($col2, 362, 'ОГНОО · '.mb_strtoupper($weekday), $this->mono, 13, 'ffffff', 80);
        $this->tiles($x, 380, $flight->origin.'>'.$flight->destination, 46, 66, 32);
        $this->tiles($col2, 380, $local->format('d.m'), 46, 66, 32);

        // Доод мөр: хүн, машин, ачаа.
        $this->line(496);
        $sx = $x;
        foreach ([[$stats['people'], $arrival ? 'хүн ирнэ' : 'хүн явна'], [$stats['rides'], 'машин'], [$stats['parcels'], 'ачаа']] as [$n, $label]) {
            $sx = $this->text($sx, 566, (string) $n, $this->sans, 30, 'ffb81c') + 12;
            $sx = $this->text($sx, 566, $label, $this->sans, 21, 'ffffff', 30) + 44;
        }
        $this->textRight(self::W - self::PAD, 566, 'om137.de', $this->mono, 21, 'ffffff');

        ob_start();
        imagepng($this->img, null, 6);
        imagedestroy($this->img);

        return (string) ob_get_clean();
    }

    /** Текстийг хавтангуудаар зурна; хоосон зайг алгасна. */
    private function tiles(int $x, int $y, string $text, int $w, int $h, int $size, int $alpha = 0): void
    {
        foreach (mb_str_split(mb_strtoupper($text)) as $ch) {
            if ($ch !== ' ') {
                $this->tile($x, $y, $w, $h, $ch, $size, 'ffb81c', ['202226', '18191c'], $alpha);
            }
            $x += $w + (int) round($w * 0.1);
        }
    }

    /** @param array{0: string, 1: string} $bg дээд, доод хагасын өнгө */
    private function tile(int $x, int $y, int $w, int $h, string $ch, int $size, string $color, array $bg, int $alpha = 0): void
    {
        $mid = $y + intdiv($h, 2);
        imagefilledrectangle($this->img, $x, $y, $x + $w - 1, $mid - 1, $this->rgb($bg[0]));
        imagefilledrectangle($this->img, $x, $mid, $x + $w - 1, $y + $h - 1, $this->rgb($bg[1]));

        $box = imagettfbbox($size, 0, $this->mono, $ch);
        $tw = $box[2] - $box[0];
        // Том үсгийн өндөр ~0.72em: босоо тэнхлэгт голлуулна.
        $baseline = $mid + (int) round($size * 0.72 * 96 / 72 / 2);
        imagettftext($this->img, $size, 0, $x + intdiv($w - $tw, 2) - $box[0], $baseline, $this->rgb($color, $alpha), $this->mono, $ch);

        imagefilledrectangle($this->img, $x, $mid - 1, $x + $w - 1, $mid, $this->rgb('000000', 20));
    }

    /** @return int текстийн баруун ирмэгийн x */
    private function text(int $x, int $y, string $text, string $font, int $size, string $color, int $alpha = 0): int
    {
        $box = imagettftext($this->img, $size, 0, $x, $y, $this->rgb($color, $alpha), $font, $text);

        return $box[2];
    }

    private function textRight(int $right, int $y, string $text, string $font, int $size, string $color, int $alpha = 0): void
    {
        $box = imagettfbbox($size, 0, $font, $text);
        $this->text($right - ($box[2] - $box[0]), $y, $text, $font, $size, $color, $alpha);
    }

    private function line(int $y): void
    {
        imagefilledrectangle($this->img, self::PAD, $y, self::W - self::PAD, $y, $this->rgb('272a2f'));
    }

    /** @param int $alpha 0 = бүрэн харагдана, 127 = тунгалаг */
    private function rgb(string $hex, int $alpha = 0): int
    {
        [$r, $g, $b] = sscanf($hex, '%02x%02x%02x');

        return imagecolorallocatealpha($this->img, $r, $g, $b, $alpha);
    }
}
