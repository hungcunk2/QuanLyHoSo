<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class ChatAttachmentStorage
{
    /** Đường dẫn tương đối trong public/ (không cần storage:link). */
    public const PUBLIC_PREFIX = 'uploads/messages';

    public static function isPublicPath(string $path): bool
    {
        return str_starts_with($path, self::PUBLIC_PREFIX.'/');
    }

    public static function store(UploadedFile $file, int $conversationId): array
    {
        $relativeDir = self::PUBLIC_PREFIX.'/'.$conversationId;
        $absoluteDir = public_path($relativeDir);

        if (! is_dir($absoluteDir)) {
            mkdir($absoluteDir, 0755, true);
        }

        $originalName = $file->getClientOriginalName();
        $mime = $file->getMimeType() ?: 'application/octet-stream';
        $safeBase = preg_replace('/[^\pL\pN._-]+/u', '_', pathinfo($originalName, PATHINFO_FILENAME)) ?: 'file';
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'bin');
        $filename = uniqid('', true).'_'.mb_substr($safeBase, 0, 80).'.'.$extension;

        $file->move($absoluteDir, $filename);

        $relative = $relativeDir.'/'.$filename;

        return [
            'attachment_path' => $relative,
            'attachment_original_name' => $originalName,
            'attachment_mime' => $mime,
            'attachment_type' => str_starts_with($mime, 'image/') ? 'image' : 'file',
        ];
    }

    public static function exists(string $path): bool
    {
        if (self::isPublicPath($path)) {
            return is_file(public_path($path));
        }

        return Storage::disk('public')->exists($path);
    }

    public static function response(
        string $path,
        string $filename,
        ?string $mime,
        bool $forceDownload
    ): Response {
        $disposition = $forceDownload ? 'attachment' : 'inline';
        $headers = [
            'Content-Type' => $mime ?: 'application/octet-stream',
            'Content-Disposition' => $disposition.'; filename="'.$filename.'"',
        ];

        if (self::isPublicPath($path)) {
            $full = public_path($path);
            if (! is_file($full)) {
                abort(404);
            }

            return response()->file($full, $headers);
        }

        if (! Storage::disk('public')->exists($path)) {
            abort(404);
        }

        return Storage::disk('public')->response($path, $filename, $headers);
    }
}
