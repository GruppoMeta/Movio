<?php

class org_glizy_dataAccessDoctrine_Query_Expression_SqlExpression
{
    protected $value;

    /**
     * Initializes a new <tt>SqlExpression</tt>.
     *
     * @param SQL string
     */
    public function __construct($value)
    {
        $this->value =  $value;
    }


    /**
     * Retrieve the string representation of expression.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->value{0} === '(' ? $this->value : '(' . $this->value . ')';
    }
}
