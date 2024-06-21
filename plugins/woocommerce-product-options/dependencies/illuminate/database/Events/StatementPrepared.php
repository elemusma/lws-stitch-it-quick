<?php

namespace Barn2\Plugin\WC_Product_Options\Dependencies\Illuminate\Database\Events;

/** @internal */
class StatementPrepared
{
    /**
     * The database connection instance.
     *
     * @var \Illuminate\Database\Connection
     */
    public $connection;
    /**
     * The PDO statement.
     *
     * @var \PDOStatement
     */
    public $statement;
    /**
     * Create a new event instance.
     *
     * @param  \Illuminate\Database\Connection  $connection
     * @param  \PDOStatement  $statement
     * @return void
     */
    public function __construct($connection, $statement)
    {
        $this->statement = $statement;
        $this->connection = $connection;
    }
}
