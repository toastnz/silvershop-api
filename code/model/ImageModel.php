<?php

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
            $this->image = Image::get_by_id('Image', $id);

            if ($this->image && $this->image->exists()) {
                // Set the initial properties
                $this->image_id = $this->image->ID;
                $this->alt      = $this->image->Title;

                $this->sizes = [
                    'small' => [
                        'src' => $this->image->CroppedImage(640, 360)->getAbsoluteURL(),
                        'width' => 640,
                        'height' => 360
                    ],
                    'medium' => [
                        'src' => $this->image->CroppedImage(320, 210)->getAbsoluteURL(),
                        'width' => 320,
                        'height' => 210
                    ],
                    'large' => [
                        'src' => $this->image->CroppedImage(160, 90)->getAbsoluteURL(),
                        'width' => 160,
                        'height' => 90
                    ],
                ];
            } else {
                $this->image_id = 0;
                $this->alt      = 'Placeholder';
                // Placeholders
                $this->sizes = [
                    'small' => [
                        'src' => 'http://placehold.it/640x360',
                        'width' => 640,
                        'height' => 360
                    ],
                    'medium' => [
                        'src' => 'http://placehold.it/320x210',
                        'width' => 320,
                        'height' => 210
                    ],
                    'large' => [
                        'src' => 'http://placehold.it/160x90',
                        'width' => 160,
                        'height' => 90
                    ],
                ];
            }
        }
    }
}
