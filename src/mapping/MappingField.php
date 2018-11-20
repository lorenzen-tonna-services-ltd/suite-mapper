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
    private $function;

    /**
     * @var string
     */
    private $data;

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
     * @return string
     */
    public function getFunction()
    {
        return $this->function;
    }

    /**
     * @param string $function
     */
    public function setFunction($function)
    {
        $this->function = $function;
    }

    /**
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param string $data
     */
    public function setData($data)
    {
        $this->data = $data;
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
            'function' => $this->function,
            'data' => $this->data
        ];
    }

    public function fromArray(array $data)
    {
        $this->converter = (new ConverterRegistry())->getConverterByKey($data['converter']);

        foreach (['sourceField', 'destinationField', 'isCustom', 'data', 'function'] as $key) {
            if (isset($data[$key])) {
                $this->$key = $data[$key];
            }
        }

        return $this;
    }
}
