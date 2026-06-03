<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\ImageManager;

trait HandlesCoverImage
{
    /**
     * Нүүр зургийн upload/устгалыг боловсруулж, $data['cover_image']-д
     * бүтэн URL (эсвэл null) оноож, 'cover'/'remove_cover' түлхүүрийг арилгана.
     * Зураг ороогүй бол cover_image-г хөндөхгүй (хуучин утга хэвээр).
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function applyCover(Request $request, array $data, string $folder, ?string $existingUrl = null): array
    {
        $remove = $request->boolean('remove_cover');
        unset($data['cover'], $data['remove_cover']);

        if ($request->hasFile('cover')) {
            $this->deleteCoverUrl($existingUrl);
            $data['cover_image'] = $this->storeResizedImage($request->file('cover'), $folder);
        } elseif ($remove) {
            $this->deleteCoverUrl($existingUrl);
            $data['cover_image'] = null;
        }

        return $data;
    }

    /**
     * Зургийг жижигрүүлж (max өргөн), шахаж storage-д хадгалаад бүтэн URL буцаана.
     */
    protected function storeResizedImage(UploadedFile $file, string $folder, int $maxWidth = 1600, int $quality = 82): string
    {
        $path = $folder.'/'.Str::random(40).'.jpg';

        try {
            $manager = new ImageManager(new Driver());
            $image = $manager->decode($file->getRealPath());
            $image->scaleDown(width: $maxWidth); // томруулахгүй, зөвхөн жижигрүүлнэ
            Storage::disk('public')->put($path, (string) $image->encode(new JpegEncoder(quality: $quality)));
        } catch (\Throwable $e) {
            // Боловсруулалт амжилтгүй бол анхны файлыг шууд хадгална.
            $path = $file->store($folder, 'public');
        }

        return Storage::disk('public')->url($path);
    }

    /**
     * Хадгалсан (өөрийн storage) зургийг устгана. Гадны URL бол алгасна.
     */
    protected function deleteCoverUrl(?string $url): void
    {
        if ($url && str_contains($url, '/storage/')) {
            Storage::disk('public')->delete(Str::after($url, '/storage/'));
        }
    }

    /**
     * Rich text (HTML) доторх <img> зургуудаас өөрийн storage-д байгаа URL-уудыг гаргана.
     *
     * @return array<int, string>
     */
    protected function bodyImageUrls(?string $html): array
    {
        if (! $html) {
            return [];
        }

        preg_match_all('/<img[^>]+src=["\']([^"\']+)["\']/i', $html, $matches);

        return array_values(array_unique(array_filter(
            $matches[1] ?? [],
            fn ($url) => str_contains($url, '/storage/'),
        )));
    }

    /**
     * Хуучин HTML-д байсан боловч шинэ HTML-д байхгүй болсон контент зургуудыг storage-аас устгана.
     */
    protected function deleteRemovedBodyImages(?string $oldHtml, ?string $newHtml): void
    {
        $removed = array_diff($this->bodyImageUrls($oldHtml), $this->bodyImageUrls($newHtml));

        foreach ($removed as $url) {
            $this->deleteCoverUrl($url);
        }
    }
}
