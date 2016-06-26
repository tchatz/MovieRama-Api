<?php

class VotingSystem extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     */
    public $movie;

    /**
     *
     * @var double
     */
    public $LIKES;

    /**
     *
     * @var double
     */
    public $HATES;

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'voting_system';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return VotingSystem[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return VotingSystem
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
