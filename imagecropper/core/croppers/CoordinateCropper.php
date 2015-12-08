<?php
namespace Core\Croppers;

class CoordinateCropper extends ImagineCropper
{
    protected $coordinates;

    /**
     * CoordinateCropper constructor.
     * @param array $coordinates
     */
    public function __construct($coordinates = array())
    {
        parent::__construct();
        $this->coordinates = $coordinates;
    }

    /**
     * Perform the image crop
     */
    public function performCrop()
    {
        $this->image = $this->image->crop($this->coordinates['x1'], $this->coordinates['x2'], $this->coordinates['y1'], $this->coordinates['y2']);
    }
}