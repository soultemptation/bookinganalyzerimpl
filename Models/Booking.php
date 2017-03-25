<?php
class Booking
{
    private $id;
    private $fields = [];

    /**
     * Booking constructor.
     * @param $id int ID of the booking
     * @param $dataTypeCluster DataTypeCluster Data type cluster of  the booking data.
     */
    public function __construct(int $id, DataTypeCluster $dataTypeCluster)
    {
        $this->id = $id;
        foreach ($dataTypeCluster->getIntegerFields() as $key => $value) {
            array_push($this->fields, new IntegerField($key, $value));
        }
        foreach ($dataTypeCluster->getBooleanFields() as $key => $value) {
            array_push($this->fields, new BooleanField($key, $value));
        }
        foreach ($dataTypeCluster->getFloatFields() as $key => $value) {
            array_push($this->fields, new FloatField($key, $value));
        }
        foreach ($dataTypeCluster->getStringFields() as $key => $value) {
            array_push($this->fields, new StringField($key, $value));
        }
        foreach ($dataTypeCluster->getPriceFields() as $key => $value) {
            array_push($this->fields, new PriceField($key, $value));
        }
        foreach ($dataTypeCluster->getDistanceFields() as $key => $value) {
            array_push($this->fields, new DistanceField($key, $value));
        }
    }

    public function getId() : int
    {
        return $this->id;
    }

    public function getFields()
    {
        return $this->fields;
    }

    /**
     * Get fields by a given type.
     * @param string $type Field type to get.
     * @return array Fields for the given type.
     */
    public function getFieldsByType(string $type) {
        $fields = [];
        foreach ($this->fields as $field) {
            if ($type == $field->getType()) {
                array_push($fields, $field);
            }
        }
        return $fields;
    }

    /**
     * Gets a filter by a name.
     * @param string $name Name of the filter to get.
     * @return Field Field which matches the name.
     * @throws Exception If field with the given name can not be found.
     */
    public function getFieldByName(string $name) {
        foreach ($this->fields as $field) {
            if ($name == $field->getName()) {
                return $field;
            }
        }
        throw new Exception('Field with name ' . $name . ' can not be found.');
    }
}