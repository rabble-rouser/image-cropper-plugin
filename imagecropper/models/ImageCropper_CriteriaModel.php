<?php
namespace Craft;

class ImageCropper_CriteriaModel extends BaseModel implements \Countable
{
    // Properties
    // =========================================================================

    /**
     * @var
     */
    private $_matchedElements;

    /**
     * @var
     */
    private $_matchedElementsAtOffsets;

    /**
     * @var
     */
    private $_cachedIds;

    /**
     * @var
     */
    private $_cachedTotal;



    /**
     * Returns an iterator for traversing over the elements.
     *
     * Required by the IteratorAggregate interface.
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->find());
    }

    /**
     * Returns whether an element exists at a given offset. Required by the ArrayAccess interface.
     *
     * @param mixed $offset
     *
     * @return bool
     */
    public function offsetExists($offset)
    {
        if (is_numeric($offset))
        {
            return ($this->nth($offset) !== null);
        }
        else
        {
            return parent::offsetExists($offset);
        }
    }

    /**
     * Returns the element at a given offset. Required by the ArrayAccess interface.
     *
     * @param mixed $offset
     *
     * @return mixed
     */
    public function offsetGet($offset)
    {
        if (is_numeric($offset))
        {
            return $this->nth($offset);
        }
        else
        {
            return parent::offsetGet($offset);
        }
    }

    /**
     * Sets the element at a given offset. Required by the ArrayAccess interface.
     *
     * @param mixed $offset
     * @param mixed $item
     *
     * @return null
     */
    public function offsetSet($offset, $item)
    {
        if (is_numeric($offset) && $item instanceof BaseElementModel)
        {
            $this->_matchedElementsAtOffsets[$offset] = $item;
        }
        else
        {
            return parent::offsetSet($offset, $item);
        }
    }

    /**
     * Unsets an element at a given offset. Required by the ArrayAccess interface.
     *
     * @param mixed $offset
     *
     * @return null
     */
    public function offsetUnset($offset)
    {
        if (is_numeric($offset))
        {
            unset($this->_matchedElementsAtOffsets[$offset]);
        }
        else
        {
            return parent::offsetUnset($offset);
        }
    }

    /**
     * Sets an attribute's value.
     *
     * In addition, will clears the cached values when a new attribute is set.
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return bool
     */
    public function setAttribute($name, $value)
    {
        // If this is an attribute, and the value is not actually changing, just return true so the matched elements
        // don't get cleared.
        if (in_array($name, $this->attributeNames()) && $this->getAttribute($name) === $value)
        {
            return true;
        }

        if (parent::setAttribute($name, $value))
        {
            $this->_matchedElements = null;
            $this->_matchedElementsAtOffsets = null;
            $this->_cachedIds = null;
            $this->_cachedTotal = null;

            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * Returns an element at a specific offset.
     *
     * @param int $offset The offset.
     *
     * @return BaseElementModel|null The element, if there is one.
     */
    public function nth($offset)
    {
        if (!isset($this->_matchedElementsAtOffsets) || !array_key_exists($offset, $this->_matchedElementsAtOffsets))
        {
            $class = get_class($this);
            $criteria = new $class($this->getAttributes());
            $criteria->offset = $offset;
            $criteria->limit = 1;
            $elements = $criteria->find();

            if ($elements)
            {
                $this->_matchedElementsAtOffsets[$offset] = $elements[0];
            }
            else
            {
                $this->_matchedElementsAtOffsets[$offset] = null;
            }
        }

        return $this->_matchedElementsAtOffsets[$offset];
    }

    /**
     * Returns the first element that matches the criteria.
     *
     * @param array|null $attributes
     *
     * @return BaseElementModel|null
     */
    public function first($attributes = null)
    {
        $this->setAttributes($attributes);

        return $this->nth(0);
    }

    /**
     * Returns the last element that matches the criteria.
     *
     * @param array|null $attributes
     *
     * @return BaseElementModel|null
     */
    public function last($attributes = null)
    {
        $this->setAttributes($attributes);

        $total = $this->total();

        if ($total)
        {
            return $this->nth($total-1);
        }
    }

    /**
     * Returns all element IDs that match the criteria.
     *
     * @param array|null $attributes
     *
     * @return array
     */
    public function ids($attributes = null)
    {
        $this->setAttributes($attributes);

        $this->_includeInTemplateCaches();

        if (!isset($this->_cachedIds))
        {
            $this->_cachedIds = craft()->imageCropper->getPreppedAssets($this, true);
        }

        return $this->_cachedIds;
    }

    /**
     * Returns the total elements that match the criteria.
     *
     * @param array|null $attributes
     *
     * @return int
     */
    public function total($attributes = null)
    {
        $this->setAttributes($attributes);

        $this->_includeInTemplateCaches();

        if (!isset($this->_cachedTotal))
        {
            $this->_cachedTotal = craft()->elements->getTotalElements($this);
        }

        return $this->_cachedTotal;
    }

    /**
     * Returns a copy of this model.
     *
     * @return BaseModel
     */
    public function copy()
    {
        $class = get_class($this);
        $copy = new $class($this->getAttributes());

        if ($this->_matchedElements !== null)
        {
            $copy->setMatchedElements($this->_matchedElements);
        }

        return $copy;
    }

    /**
     * Stores the matched elements to avoid redundant DB queries.
     *
     * @param array $elements The matched elements.
     *
     * @return null
     */
    public function setMatchedElements($elements)
    {
        $this->_matchedElements = $elements;

        // Store them by offset, too
        $offset = $this->offset;

        foreach ($this->_matchedElements as $element)
        {
            $this->_matchedElementsAtOffsets[$offset] = $element;
            $offset++;
        }
    }

    /**
     * Returns the total number of elements matched by this criteria. Required by the Countable interface.
     *
     * @return int
     */
    public function count()
    {
        return count($this->find());
    }

    /**
     * Returns all elements that match the criteria.
     *
     * @param array $attributes Any last-minute parameters that should be added.
     *
     * @return array The matched elements.
     */
    public function find($attributes = null)
    {
        $this->setAttributes($attributes);

        $this->_includeInTemplateCaches();

        if (!isset($this->_matchedElements))
        {
            $elements = craft()->imageCropper->getPreppedAssets($this);
            $this->setMatchedElements($elements);
        }

        return $this->_matchedElements;
    }

    // Custom Criteria Methods

    /**
     * Get all original images
     * @return array
     */
    public function getImages()
    {
        $models = $this->find();
        $assets = array();
        foreach($models as $model){
            $assets[] = $model['image'];
        }
        return $assets;

    }

    /**
     * Get all crop images
     * @return array
     */
    public function getCrops()
    {
        $models = $this->find();
        $assets = array();
        foreach($models as $model){
            $assets[] = $model['crops'];
        }
        return $assets;
    }

    /**
     * Get a crop based on a dimension
     * @param $dimensions
     * @return array|bool
     */
    public function getCrop($dimensions)
    {
        $width = null;
        $height = null;
        if(!is_array($dimensions)){
            return false;
        }
        if(isset($dimensions['width']) && !empty($dimensions['width']))
        {
            $width = $dimensions['width'];
        }
        if(isset($dimensions['height']) && !empty($dimensions['height'])){
            $height = $dimensions['height'];
        }
        if(empty($width) or empty($height)){
            return false;
        }
        $cropGroups = $this->getCrops();
        $data = array();
        foreach($cropGroups as $assets){
            foreach($assets as $asset){
                if($asset->width == $width && $asset->height == $height){
                    $data[] = $asset;
                }
            }

        }
        if(count($data) == 1){
            $data = $data[0];
        }
        return $data;
    }

    // Protected Methods
    // =========================================================================

    /**
     * @inheritDoc BaseModel::defineAttributes()
     *
     * @return array
     */
    protected function defineAttributes()
    {
        $attributes = array(
            'element'          => AttributeType::Number,
            'section'          => AttributeType::String,
            'field'            => AttributeType::String,
            'limit'            => array(AttributeType::Number, 'default' => 100),
            'offset'           => array(AttributeType::Number, 'default' => 0),
            'order'            => array(AttributeType::String, 'default' => 'sortOrder'),
        );

        return $attributes;
    }

    // Private Methods
    // =========================================================================

    /**
     * @return null
     */
    private function _includeInTemplateCaches()
    {
        $cacheService = craft()->getComponent('templateCache', false);

        if ($cacheService)
        {
            $cacheService->includeCriteriaInTemplateCaches($this);
        }
    }
}