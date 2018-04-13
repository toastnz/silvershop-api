<?php

namespace Toast\ShopAPI\Model;

use SilverStripe\Assets\Image;
use SilverStripe\Assets\Image_Backend;
use SilverStripe\Core\Config\Config;

/**
 * Class ImageModel
 */
class ImageModel extends ShopModelBase
{
    /** @var Image $image */
    protected $image;

    protected $image_id;
    protected $alt;
    protected $orientation;
    protected $sizes = [];

    protected static $fields = [
        'alt',
        'sizes',
        'orientation'
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

                switch ($this->image->getOrientation()) {
                    case Image_Backend::ORIENTATION_LANDSCAPE:
                        $this->orientation = 'wider';
                        break;
                    case Image_Backend::ORIENTATION_PORTRAIT:
                        $this->orientation = 'taller';
                        break;
                    default:
                        $this->orientation = 'square';
                        break;
                }

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
        $sizes = Config::inst()->get(ImageModel::class, 'image_sizes');

        $cropType = Config::inst()->get(ImageModel::class, 'crop_type') ?: 'FitMax';

        $images = [];

        if (is_array($sizes)) {
            foreach ($sizes as $size => $d) {
                if (isset($d['width']) && isset($d['height'])) {

                    if ($this->image && $this->image->exists()) {
                        $images[$size] = [
                            'src'         => $this->image->{$cropType}($d['width'], $d['height'])->getAbsoluteURL(),
                            'width'       => $d['width'],
                            'height'      => $d['height']
                        ];
                    } else {
                        $images[$size] = [
                            'src'    => sprintf('http://placehold.it/%sx%s', $d['width'], $d['height']),
                            'width'  => $d['width'],
                            'height' => $d['height']
                        ];
                    }
                }
            }
        }

        return $images;
    }
}
