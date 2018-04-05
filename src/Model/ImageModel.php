<?php

namespace Toast\ShopAPI\Model;

use SilverStripe\Assets\Image;

/**
 * Class ImageModel
 */
class ImageModel extends ShopModelBase
{
    /** @var Image $image */
    protected $image;

    protected $image_id;
    protected $alt;
    protected $sizes = [];

    protected static $fields = [
        'alt',
        'sizes'
    ];

    public function __construct($id)
    {
        parent::__construct();

        if ($id && is_numeric($id)) {
            // Get the image object
            $image = Image::get_by_id(Image::class, $id);

            if ($image && $image->exists()) {

                $this->extend('updateImage', $image);

                $this->image = $image;

                // Set the initial properties
                $this->image_id = $this->image->ID;
                $this->alt      = $this->image->Title;
            }

        } else {
            $this->image_id = 0;
            $this->alt      = 'Placeholder';

            $this->image = $this->extend('updateImagePlaceholder');
        }

        $this->sizes = $this->getImageSizes();
    }

    /**
     * Based on config, return an array of image sizes
     *
     * @return array
     */
    public function getImageSizes()
    {
        $sizes = self::config()->get('image_sizes');

        $images = [];

        foreach ($sizes as $size => $d) {
            if (isset($d['width']) && isset($d['height'])) {

                if ($this->image && $this->image->exists()) {
                    $images[$size] = [
                        'src' => $this->image->Fill($d['width'], $d['height'])->getAbsoluteURL(),
                        'width' => $d['width'],
                        'height' => $d['height']
                    ];
                } else {
                    $images[$size] = [
                        'src' => sprintf('http://placehold.it/%sx%s', $d['width'], $d['height']),
                        'width' => $d['width'],
                        'height' => $d['height']
                    ];
                }
            }
        }

        return $images;
    }
}
