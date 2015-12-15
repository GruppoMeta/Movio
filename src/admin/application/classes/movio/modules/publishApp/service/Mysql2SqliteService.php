<?php
class movio_modules_publishApp_service_Mysql2SqliteService extends GlizyObject
{
    function onRegister()
    {
        
    }
    
    public function convert($dbHost, $dbUser, $dbPass, $dbName, $tables, $sqliteDb)
    {
        try {
        	$options = array( PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8' );
        	$mysqli = new PDO( "mysql:host=".$dbHost.";dbname=".$dbName, $dbUser, $dbPass, $options );
        } catch( PDOException $e ) {
        	$this->showMessageAndDie( $e->getMessage() );
        }
        
        try {
            @unlink( $sqliteDb );
            $sqlite = new PDO( "sqlite:".$sqliteDb );
        } catch( PDOException $e ) {
            $this->showMessageAndDie($e->getMessage());
        }

        foreach($tables as $table) {
            $this->converTable($mysqli, $sqlite, $table['name'], $table['type'] === 'fulltext');
        }      
    }
    
    private function converTable( $mysqli, $sqlite, $tableName, $fts3 = false)
    {
    	$createFields = array();
    	$pkFields = array();
    	$indexFields = array();
    	$tableFields = array();
    	
    	foreach ( $mysqli->query( "SHOW COLUMNS FROM ".$tableName ) as $row )
    	{
    		$tableFields[] = $row[ "Field" ];
    		$fieldType = "TEXT";
    		if ( stripos( $row[ "Type" ], "int(" ) !== false )
    		{
    			$fieldType = "INTEGER";
    		}
    		elseif ( stripos( $row[ "Type" ], "datetime") !== false )
    		{
    			$fieldType = "DATETIME";
    		}
    		elseif ( stripos( $row[ "Type" ], "date" ) !== false )
    		{
    			$fieldType = "DATE";
    		}
    
    		if ( $row[ "Key" ] == "PRI" )
    		{
    		    if ($fts3 == true) {
    		        // non considera questo campo
    			    array_pop($tableFields);
    			    continue;
    			}
    			
    			//$fieldType = "INTEGER";	
    			$pkFields[] = $row[ "Field" ];
    		}
    		else if ( $row[ "Key" ] == "MUL" )
    		{
    			$indexFields[] = "CREATE INDEX ".$row[ "Field" ]."_index ON ".$tableName."(".$row[ "Field" ].")";
    		}
    		
    	    $createFields[] = $row[ "Field" ]." ".$fieldType;
    	}
    	
        if ($fts3 == true) {
    	    $sqlCreate= "CREATE VIRTUAL TABLE ".$tableName." USING fts3(".implode(",", $createFields).")";
        } else {
            if ( count( $pkFields ) ) {
        		array_push( $createFields, "PRIMARY KEY (".implode( ",", $pkFields ).")" );
        	}
    	    $sqlCreate= "CREATE TABLE ".$tableName." (".implode(",", $createFields).")";
        }
        

	    // create the table
	    $r = $sqlite->exec( $sqlCreate );
	    if ($r === false) {
	       $this->showMessageAndDie( print_r( $sqlite->errorInfo(), true) );
	    }

    	// insert statement
    	$insertSqlPart = str_repeat( "?,", count( $tableFields ) );
    	$insertSqlPart = substr( $insertSqlPart, 0, -1 );
    	$insertSql = "INSERT INTO ".$tableName."(".implode(",", $tableFields).") VALUES ( ".$insertSqlPart." ) ";
    	$sth = $sqlite->prepare( $insertSql );
    	
    	// get the number of records in the table
    	$sthCount = $mysqli->query( "SELECT count(*) FROM ".$tableName );
    	$row = $sthCount->fetch();
    	$numRows = $row[ 0 ];
    	$sthCount->closeCursor();
    
    	// read and convert all records
    	$pageLength = 100000;
    	$currentPage = 0;
    	$i = 0;
    	while ( true )
    	{
    		$sqlite->beginTransaction();
    		foreach ( $mysqli->query( "SELECT * FROM ".$tableName." LIMIT ".$currentPage.",".$pageLength ) as $row )
    		{
    			$params = array();
    			foreach( $tableFields as $v )
    			{
    				$params[] = $row[ $v ];
    			}
    		
    			$r = $sth->execute( $params );
    			if ( !$r )
    			{
    				// error
    				$this->showMessageAndDie( print_r( $sqlite->errorInfo(), true) );
    			}
    		
    			$i++;
    		}
    		$sqlite->commit();
    
    		if ( $i < $numRows )
    		{
    			echo ".";
    			$currentPage += $pageLength;
    		}
    		else
    		{
    			break;
    		}
    	}
    	
    	// create index
    	if ( count( $indexFields ) )
    	{
    		$sqlite->exec( implode( ";", $indexFields ) );
    	}
    }
    
    private function showMessage($message)
    {
    	echo $message."\n";
    }
    
    private function showMessageAndDie($message)
    {
    	die( $message."\n\n" );
    }
}