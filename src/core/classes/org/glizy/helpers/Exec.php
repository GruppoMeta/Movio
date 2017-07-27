<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

class org_glizy_helpers_Exec
{
    /**
     * Esecuzione comando su shell
     * @param  string $cmd   comando da eseguire
     * @param  string $input parametri da passare al comando
     * @return array        array contenente stdout, stderr, return
     */
    static public function exec($cmd, $input='')
    {
        $proc = proc_open($cmd, array(0=>array('pipe', 'r'), 1=>array('pipe', 'w'), 2=>array('pipe', 'w')), $pipes);
        fwrite($pipes[0], $input);
        fclose($pipes[0]);
        $stdout = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);
        $rtn = proc_close($proc);
        return array(   'stdout'=>$stdout,
                        'stderr'=>$stderr,
                        'return'=>$rtn
                    );
    }
}
