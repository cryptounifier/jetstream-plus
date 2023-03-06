<?php

namespace CryptoUnifier\JetstreamPlus\Traits;

use Intervention\Image\ImageManagerStatic as Image;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

use Laravel\Jetstream\Features;

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
        // Format, resize image and save temporarily
        // TODO: Support gif, the currently library do not support animated gif resizing
        Image::make($photo->get())->resize(256, 256)->save($photo->getPathname(), 90, 'jpg');

        // Upload image publicly
        // TODO: Check if because the image changes to jpg, the $photo class need to updated
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

        Storage::disk($this->profilePhotoDisk())->delete($this->profile_photo_path);

        $this->forceFill([
            'profile_photo_path' => null,
        ])->save();
    }

    /**
     * Get the URL to the user's profile photo.
     *
     * @return string
     */
    public function getProfilePhotoUrlAttribute()
    {
        return $this->profile_photo_path
                    ? Storage::disk($this->profilePhotoDisk())->url($this->profile_photo_path)
                    : $this->defaultProfilePhotoUrl();
    }

    /**
     * Get the default profile photo URL if no profile photo has been uploaded.
     *
     * @return string
     */
    protected function defaultProfilePhotoUrl()
    {
        return 'https://www.gravatar.com/avatar/' . urlencode($this->email) . '?s=128&d=retro';
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
