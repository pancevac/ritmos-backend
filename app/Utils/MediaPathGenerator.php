<?php

namespace App\Utils;


use Carbon\Carbon;
use Spatie\MediaLibrary\Models\Media;
use Spatie\MediaLibrary\PathGenerator\BasePathGenerator;

class MediaPathGenerator extends BasePathGenerator
{
    /**
     * Folder path.
     *
     * @var string
     */
    protected $path;

    /*
     * Get a (unique) base path for the given media.
     */
    protected function getBasePath(Media $media): string
    {
        if ($media->hasCustomProperty('path')) {

            // If image have path property, this means that we retrieve the image,
            // so put it into $this->path...
            $this->path = $media->getCustomProperty('path');
        } else {

            // If image doesn't have path property, means that we storing image
            // so call getFolderName method which will determine path where will image be stored.
            $this->path = $this->getFolderName($media);

            // Save path into image custom property for later retrieving.
            $media->setCustomProperty('path', $this->path);
            $media->update();
        }

        return $this->path . $media->getKey();
    }

    /**
     * Get folder name based on model name (plural) which is related to this media.
     * In other words, if we store image for products, if products have model "Product", folder name will be 'products' (plural).
     *
     * @param Media $media
     * @return string
     */
    protected function getFolderName(Media $media)
    {
        // Get model name as string
        $className = class_basename($media->model);

        // Return plural of model name + lower-cased and add currently year and month.
        return str_plural(strtolower($className)) . '/' .
            $media->collection_name . '/' .
            Carbon::now()->format('Y/M') . '/';
    }
}