<?php

namespace App\Downloader;

use File;
use Illuminate\Support\Facades\Http;
use Str;

class DirectDownloader
{
    protected string|null $url;
    protected array $mimes;

    public function __construct(string|null $url = null)
    {
        $this->mimes = config('downloader.direct.mimes');
        $this->url = $url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;
        return $this;
    }

    public function validate(string|null $url = null): bool
    {
        $url = $url ?? $this->url;

        if (!$url) {
            throw new \Exception('Please provide a valid URL!');
        }

        $isValid = false;

        foreach ($this->mimes as $mime) {
            if (Str::contains($url, ".$mime")) {
                $isValid = true;
            }
        }

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            $isValid = false;
        }

        return $isValid;
    }

    public function getExtension(): string|null
    {
        $url = $url ?? $this->url;

        if (!$url) {
            throw new \Exception('Please provide a valid URL!');
        }

        $extension = null;
        foreach ($this->mimes as $mime) {
            if (Str::contains($this->url, ".$mime")) {
                $extension = $mime;
            }
        }

        return $extension;
    }

    public function getType(): string
    {
        $url = $url ?? $this->url;

        if (!$url) {
            throw new \Exception('Please provide a valid URL!');
        }

        return $this->getExtension() == 'mp3' ? 'audio' : 'video';
    }

    public function download(string|null $url = null): string
    {
        $url = $url ?? $this->url;

        if (!$url) {
            throw new \Exception('Please provide a valid URL!');
        }

        $tempFile = tempnam(sys_get_temp_dir(), 'test.zip');
        $file = Http::withoutVerifying()->timeout(300)->send('GET', $url, ['sink' => $tempFile]);
        $fileName = Str::uuid() . '.' . $this->getExtension();
        $path = "downloads/$fileName";

        if (!File::exists(public_path('downloads'))) {
            File::makeDirectory(public_path('downloads'));
        }

        File::put(public_path($path), $file->body());

        return public_path($path);
//        return asset($path);
//        return config('app.url') . '/' . $path;
    }
}
