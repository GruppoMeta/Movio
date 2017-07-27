<?php

use \Doctrine\DBAL\Connection,
    \Doctrine\DBAL\Query\Expression\CompositeExpression;

class org_glizy_dataAccessDoctrine_Query_Expression_ExpressionBuilder extends \Doctrine\DBAL\Query\Expression\ExpressionBuilder
{
    protected $conn = null;

    /**
     * Initializes a new <tt>ExpressionBuilder</tt>.
     *
     * @param \Doctrine\DBAL\Connection $connection DBAL Connection
     */
    public function __construct(Connection $connection)
    {
        parent::__construct($connection);
        $this->conn =  $connection;
    }

    /**
     * Creates a comparison expression.
     *
     * @param mixed $x Left expression
     * @param string $operator One of the ExpressionBuilder::* constants.
     * @param mixed $y Right expression
     * @return string
     */
    public function comparison($x, $operator, $y, $cast = true)
    {
        if (strtoupper($operator) == 'LIKE') {
            return $this->like($x, $y, $cast);
        }

        return $this->conn->quoteIdentifier($x) . ' ' . $operator . ' ' . $this->conn->quoteIdentifier($y);
    }

    /**
     * Creates an IS NULL expression with the given arguments.
     *
     * @param string $x Field in string format to be restricted by IS NULL
     *
     * @return string
     */
    public function isNull($x)
    {
        return $this->conn->quoteIdentifier($x) . ' IS NULL';
    }

    /**
     * Creates an IS NOT NULL expression with the given arguments.
     *
     * @param string $x Field in string format to be restricted by IS NOT NULL
     *
     * @return string
     */
    public function isNotNull($x)
    {
        return $this->conn->quoteIdentifier($x) . ' IS NOT NULL';
    }

    /**
     * Creates a LIKE() comparison expression with the given arguments.
     *
     * @param string $x Field in string format to be inspected by LIKE() comparison.
     * @param mixed $y Argument to be used in LIKE() comparison.
     *
     * @return string
     */
    public function like($x, $y, $cast = true)
    {
        if ($cast) {
            return 'CAST('.$this->conn->quoteIdentifier($x) . ' AS CHAR(255)) LIKE ' . $this->conn->quoteIdentifier($y);
        }
        else {
            return $this->conn->quoteIdentifier($x) . ' LIKE ' . $this->conn->quoteIdentifier($y);
        }
    }

    public function sql($x)
    {
        return new org_glizy_dataAccessDoctrine_Query_Expression_SqlExpression($x);
    }
}
