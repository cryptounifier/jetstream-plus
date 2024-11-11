<?php

namespace CryptoUnifier\JetstreamPlus\Traits;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use Laravel\Jetstream\Features;

use Illuminate\Database\Eloquent\Casts\Attribute;

trait HasProfilePhoto
{
    /**
     * Sets the users profile photo from a URL.
     */
    public function setProfilePhotoFromUrl(string $url): void
    {
        $name = pathinfo($url)['basename'];
        file_put_contents($file = '/tmp/' . $name, file_get_contents($url));
        $this->updateProfilePhoto(new UploadedFile($file, $name));
    }

    /**
     * Update the user's profile photo.
     */
    public function updateProfilePhoto(UploadedFile $photo, string $storagePath = 'profile-photos'): void
    {
        $manager = new ImageManager(new Driver());

        if ($photo->extension() === 'gif') {
            $manager->read($photo->get())
                ->resize(128, 128)
                ->toGif()
                ->save($photo->getPathname());
        } else {
            $manager->read($photo->get())
                ->resize(128, 128)
                ->toJpeg(90)
                ->save($photo->getPathname());
        }

        tap($this->profile_photo_path, function ($previous) use ($photo, $storagePath): void {
            $this->forceFill([
                'profile_photo_path' => $photo->storePublicly(
                    $storagePath,
                    ['disk' => $this->profilePhotoDisk()]
                ),
            ])->save();

            if ($previous) {
                Storage::disk($this->profilePhotoDisk())->delete($previous);
            }
        });
    }

    /**
     * Delete the user's profile photo.
     */
    public function deleteProfilePhoto(): void
    {
        if (! Features::managesProfilePhotos()) {
            return;
        }

        if (is_null($this->profile_photo_path)) {
            return;
        }

        Storage::disk($this->profilePhotoDisk())->delete($this->profile_photo_path);

        $this->forceFill([
            'profile_photo_path' => null,
        ])->save();
    }

    /**
     * Get the URL to the user's profile photo.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    public function profilePhotoUrl(): Attribute
    {
        return Attribute::get(function () {
            return $this->profile_photo_path
                    ? Storage::disk($this->profilePhotoDisk())->url($this->profile_photo_path)
                    : $this->defaultProfilePhotoUrl();
        });
    }

    /**
     * Get the default profile photo URL if no profile photo has been uploaded.
     *
     * @return string
     */
    protected function defaultProfilePhotoUrl()
    {
        return 'https://www.gravatar.com/avatar/' . urlencode($this->email) . '?s=128&d=retro&r=g';
    }

    /**
     * Get the disk that profile photos should be stored on.
     *
     * @return string
     */
    protected function profilePhotoDisk()
    {
        return isset($_ENV['VAPOR_ARTIFACT_NAME']) ? 's3' : config('jetstream.profile_photo_disk', 'public');
    }
}
