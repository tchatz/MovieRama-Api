<?php 

use Phalcon\Db\Column;
use Phalcon\Db\Index;
use Phalcon\Db\Reference;
use Phalcon\Mvc\Model\Migration;

/**
 * Class VotingSystemMigration_104
 */
class VotingSystemMigration_104 extends Migration
{
    /**
     * Define the table structure
     *
     * @return void
     */
    public function morph()
    {
        $this->morphTable('voting_system', array(
                'columns' => array(
                    new Column(
                        'movie',
                        array(
                            'type' => Column::TYPE_INTEGER,
                            'notNull' => true,
                            'size' => 11,
                            'first' => true
                        )
                    ),
                    new Column(
                        'LIKES',
                        array(
                            'type' => Column::TYPE_DECIMAL,
                            'size' => 23,
                            'after' => 'movie'
                        )
                    ),
                    new Column(
                        'HATES',
                        array(
                            'type' => Column::TYPE_DECIMAL,
                            'size' => 23,
                            'after' => 'LIKES'
                        )
                    )
                ),
                'options' => array(
                    'TABLE_TYPE' => 'VIEW',
                    'AUTO_INCREMENT' => '',
                    'ENGINE' => '',
                    'TABLE_COLLATION' => ''
                ),
            )
        );
    }

    /**
     * Run the migrations
     *
     * @return void
     */
    public function up()
    {

    }

    /**
     * Reverse the migrations
     *
     * @return void
     */
    public function down()
    {

    }

}
