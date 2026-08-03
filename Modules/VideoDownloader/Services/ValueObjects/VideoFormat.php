<?php

namespace Modules\VideoDownloader\Services\ValueObjects;

class VideoFormat
{
    public function __construct(
        public readonly string  $id,
        public readonly string  $ext,
        public readonly ?string $quality,
        public readonly ?int    $height,
        public readonly ?int    $width,
        public readonly ?int    $fps,
        public readonly ?int    $filesize,
        public readonly ?string $vcodec,
        public readonly ?string $acodec,
        public readonly bool    $hasVideo,
        public readonly bool    $hasAudio,
        public readonly ?string $note,
    ) {
    }

    /**
     * Build from a yt-dlp format array entry.
     */
    public static function fromYtDlp(array $format): self
    {
        $vcodec   = $format['vcodec'] ?? 'none';
        $acodec   = $format['acodec'] ?? 'none';
        $hasVideo = $vcodec !== 'none' && ! empty($vcodec);
        $hasAudio = $acodec !== 'none' && ! empty($acodec);

        // Build quality label
        $height  = $format['height'] ?? null;
        $quality = null;

        if ($hasVideo && $height) {
            $quality = $height . 'p';
            if (isset($format['fps']) && $format['fps'] > 30) {
                $quality .= $format['fps'];
            }
        } elseif (! $hasVideo && $hasAudio) {
            $abr     = $format['abr'] ?? null;
            $quality = $abr ? round($abr) . 'kbps' : 'audio';
        }

        return new self(
            id:       $format['format_id'] ?? 'unknown',
            ext:      $format['ext'] ?? 'mp4',
            quality:  $quality,
            height:   $height ? (int) $height : null,
            width:    isset($format['width']) ? (int) $format['width'] : null,
            fps:      isset($format['fps']) ? (int) $format['fps'] : null,
            filesize: isset($format['filesize']) ? (int) $format['filesize'] :
                      (isset($format['filesize_approx']) ? (int) $format['filesize_approx'] : null),
            vcodec:   $vcodec !== 'none' ? $vcodec : null,
            acodec:   $acodec !== 'none' ? $acodec : null,
            hasVideo: $hasVideo,
            hasAudio: $hasAudio,
            note:     $format['format_note'] ?? null,
        );
    }

    public function isAudioOnly(): bool
    {
        return ! $this->hasVideo && $this->hasAudio;
    }

    public function isCombined(): bool
    {
        return $this->hasVideo && $this->hasAudio;
    }

    public function getFormattedFilesize(): string
    {
        if (! $this->filesize) return '~';
        if ($this->filesize < 1048576)    return round($this->filesize / 1024, 1) . ' KB';
        if ($this->filesize < 1073741824) return round($this->filesize / 1048576, 1) . ' MB';
        return round($this->filesize / 1073741824, 2) . ' GB';
    }

    public function toArray(): array
    {
        return [
            'id'       => $this->id,
            'ext'      => $this->ext,
            'quality'  => $this->quality,
            'height'   => $this->height,
            'width'    => $this->width,
            'fps'      => $this->fps,
            'filesize' => $this->filesize,
            'vcodec'   => $this->vcodec,
            'acodec'   => $this->acodec,
            'hasVideo' => $this->hasVideo,
            'hasAudio' => $this->hasAudio,
            'note'     => $this->note,
        ];
    }
}