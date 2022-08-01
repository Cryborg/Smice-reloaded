<?php

namespace App\Classes;

use Laravolt\Avatar\Facade as Avatar;
use Webpatser\Uuid\Uuid;
use Illuminate\Support\Facades\Storage;


class AvatarService
{
    protected $image_name;

    public function create(string $first_name, string $last_name, bool $unique_mass_name = false): string
    {
        if (!$unique_mass_name) {
            $picture = $this->getImageName();
        } else {
            $picture = $this->generateImageName();
        }

        $picture_path = base_path() . '/storage/app/images/' . $picture;
        $picture_url = url() . '/images/' . $picture;
        $file = base_path() . '/storage/app/images/' . $picture;

        Avatar::create($first_name . ' ' . $last_name)->save($picture_path);

        $path = '/images/' . $picture;
        $exists = Storage::has($path);
        if (!$exists) {
            $path2 = Storage::put($path, file_get_contents($file));
        }
        unlink($file);
        return $picture_url;
    }

    public function generateImageName(): string
    {
        return Uuid::generate(4)->string;
    }

    public function getImageName(): string
    {
        if (empty($this->image_name)) {
            $this->image_name = $this->generateImageName();
        }

        return $this->image_name;
    }

    public function getImageUrl(): string
    {
        return url() . '/images/' . $this->getImageName();
    }
}
