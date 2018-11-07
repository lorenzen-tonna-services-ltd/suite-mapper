<?php
namespace SuiteMapper\Mapping;

class MappingRelationField
{
    /**
     * @var string
     */
    private $field;

    /**
     * @var string
     */
    private $source;

    /**
     * @var string
     */
    private $function;

    /**
     * @var mixed
     */
    private $value;

    /**
     * @return string
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * @param string $field
     */
    public function setField($field)
    {
        $this->field = $field;
    }

    /**
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @param string $source
     */
    public function setSource($source)
    {
        $this->source = $source;
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
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'field' => $this->field,
            'source' => $this->source,
            'value' => $this->value,
            'function' => $this->function
        ];
    }

    /**
     * @param array $data
     * @return MappingRelationField
     */
    public function fromArray(array $data)
    {
        $this->field = $data['field'];
        $this->source = $data['source'];
        $this->value = $data['value'];
        $this->function = $data['function'];

        return $this;
    }
}