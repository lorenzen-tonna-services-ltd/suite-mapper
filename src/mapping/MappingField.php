<?php
namespace SuiteMapper\Mapping;

use SuiteMapper\Converter\Converter;
use SuiteMapper\Converter\ConverterRegistry;

class MappingField
{
    /**
     * @var Converter
     */
    private $converter;

    /**
     * @var string
     */
    private $sourceField;

    /**
     * @var string
     */
    private $destinationField;

    /**
     * @var bool
     */
    private $isCustom = false;

    /**
     * @return Converter
     */
    public function getConverter()
    {
        return $this->converter;
    }

    /**
     * @param Converter $converter
     */
    public function setConverter($converter)
    {
        $this->converter = $converter;
    }

    /**
     * @return string
     */
    public function getSourceField()
    {
        return $this->sourceField;
    }

    /**
     * @param string $sourceField
     */
    public function setSourceField($sourceField)
    {
        $this->sourceField = $sourceField;
    }

    /**
     * @return string
     */
    public function getDestinationField()
    {
        return $this->destinationField;
    }

    /**
     * @param string $destinationField
     */
    public function setDestinationField($destinationField)
    {
        $this->destinationField = $destinationField;
    }

    /**
     * @return bool
     */
    public function isCustom()
    {
        return $this->isCustom;
    }

    /**
     * @param bool $bool
     */
    public function setCustom($bool)
    {
        $this->isCustom = $bool;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $converterKey = null;
        if ($this->converter instanceof Converter) {
            $converterKey = $this->converter->getKey();
        }

        return [
            'converter' => $converterKey,
            'sourceField' => $this->sourceField,
            'destinationField' => $this->destinationField,
            'isCustom' => $this->isCustom,
        ];
    }

    public function fromArray(array $data)
    {
        $this->converter = (new ConverterRegistry())->getConverterByKey($data['converter']);
        $this->sourceField = $data['sourceField'];
        $this->destinationField = $data['destinationField'];
        
        if (isset($data['isCustom'])) {
            $this->isCustom = $data['isCustom'];
        }

        return $this;
    }
}
