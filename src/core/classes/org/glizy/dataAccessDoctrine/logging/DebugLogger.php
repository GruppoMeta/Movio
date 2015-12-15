<?php

class org_glizy_dataAccessDoctrine_logging_DebugLogger implements Doctrine\DBAL\Logging\SQLLogger
{
    /**
     * {@inheritdoc}
     */
    public function startQuery($sql, array $params = null, array $types = null)
    {
        foreach($params as $k=>$v) {
            $sql = str_replace($k, '\''.$v.'\'', $sql);
        }

        echo $sql . PHP_EOL;
    }

    /**
     * {@inheritdoc}
     */
    public function stopQuery()
    {

    }
}
