<?php
require_once(org_glizy_Paths::get('CORE_LIBS').'sql-formatter/lib/SqlFormatter.php');

use \Doctrine\DBAL\Connection,
    \Doctrine\DBAL\Query\Expression\CompositeExpression;

class org_glizy_dataAccessDoctrine_logging_EchoSQLLogger extends \Doctrine\DBAL\Logging\EchoSQLLogger
{
    public $start = null;

    /**
     * {@inheritdoc}
     */
    public function startQuery($sql, array $params = null, array $types = null)
    {
        $this->start = microtime(true);

        $replacedSql = $sql;
        if ($params) {
            foreach ($params as $param => $value) {
                $value = is_string($value) ? "'".$value."'" : $value;
                $replacedSql = str_replace($param, $value, $replacedSql);
            }
        }

        echo SqlFormatter::format($replacedSql).'</br></br>';
        parent::startQuery($sql, $params, $types);
    }

     /**
     * {@inheritdoc}
     */
    public function stopQuery()
    {
        echo 'query time: '.round((microtime(true)-$this->start), 3).'s </br></br>';
    }
}