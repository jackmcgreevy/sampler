<?php
namespace Craft;

/**
 * Entry Count Model
 */
class EntryCountModel extends BaseModel
{
    /**
     * Define what is returned when model is converted to string
     *
     * (Casts the models count to a string, and returns it.)
     *
     * @return string
     */
    public function __toString()
    {
        return (string)$this->count;
    }

    /**
     * Define model attributes
     *
     * (The model's attributes mimic the record's columns so that we
     * have a data container that we can safely pass around between
     * the individual components of the plugin. Since models don't have
     * access to the database, this is a safe way to accomplish that.)
     *
     * @return array
     */
    public function defineAttributes()
    {
        return array(
            'id' => AttributeType::Number,
            'entryId' => AttributeType::Number,
            'count' => array(AttributeType::Number, 'default' => 0),
            'dateCreated' => AttributeType::DateTime,
            'dateUpdated' => AttributeType::DateTime,
        );
    }
}
