<?php

class Votes extends \Phalcon\Mvc\Model
{
    const HATE = 0;
    const LIKE = 1;

    
    
    /**
     *
     * @var integer
     */
    protected $id;

    /**
     *
     * @var integer
     */
    protected $user;

    /**
     *
     * @var integer
     */
    protected $movie;

    /**
     *
     * @var integer
     */
    protected $vote_type;

    /**
     * Method to set the value of field id
     *
     * @param integer $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Method to set the value of field user
     *
     * @param integer $user
     * @return $this
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Method to set the value of field movie
     *
     * @param integer $movie
     * @return $this
     */
    public function setMovie($movie)
    {
        $this->movie = $movie;

        return $this;
    }

    /**
     * Method to set the value of field vote_type
     *
     * @param integer $vote_type
     * @return $this
     */
    public function setVoteType($vote_type)
    {
        $this->vote_type = $vote_type;

        return $this;
    }

    /**
     * Returns the value of field id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns the value of field user
     *
     * @return integer
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Returns the value of field movie
     *
     * @return integer
     */
    public function getMovie()
    {
        return $this->movie;
    }

    /**
     * Returns the value of field vote_type
     *
     * @return integer
     */
    public function getVoteType()
    {
        return $this->vote_type;
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->belongsTo('movie', 'Movies', 'id', array('alias' => 'Movies'));
        $this->belongsTo('user', 'Users', 'id', array('alias' => 'Users'));
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'votes';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Votes[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Votes
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

}
