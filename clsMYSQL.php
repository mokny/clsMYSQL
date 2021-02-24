<?php
/**
 * Project:     MYSQL-Class
 * File:        clsMYSQL.php
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * For questions, help, comments, discussion, etc., please join the
 * codegott mailing list. Send a blank e-mail to
 * mailme@codegott.de
 *
 * @link http://lib.codegott.de/mysql
 * @copyright 2001-2021 Till Vennefrohne
 * @author Till Vennefrohne 
 * @package clsMYSQL
 * @version 3.6.3 BUILD 4
 */

class MYSQL {

    private $Connections = array();
    private $PrimaryDatabase = false;

    /**
  	* constructor: Establishes the primary database connection
  	*
  	* @author     Till Vennefrohne
  	* @version    1.0
  	* @access	  public
  	* @param	  $username, $password, $server, $database 
  	* @return     None
  	*/

    function __construct($username, $password, $server, $database) {
        $this->AddConnection($username, $password, $server, $database, true);
    }

    /**
  	* AddConnection: Establishes another connection to a different database
  	*
  	* @author     Till Vennefrohne
  	* @version    1.0
  	* @access	  public
  	* @param	  $username, $password, $server, $database, optional bool $isprimarydatabase 
  	* @return     false / string (ConnectionID)
  	*/

    public function AddConnection($username, $password, $server, $database, $isprimarydatabase = false) {
        $conid = $server.'_'.$username.'_'.$database;

        $this->Connections[$conid] = mysqli_connect($server, $username, $password, $database);

        if (!$this->Connections[$conid]) {
            return false;
        } else {
            $this->PrimaryDatabase = $conid;
            return $conid;
        }
    }

    /**
  	* SetPrimaryDatabase: Sets the primary database if multiple databases are connected
  	*
  	* @author     Till Vennefrohne
  	* @version    1.0
  	* @access	  public
  	* @param	  $conid 
  	* @return     None
  	*/

    public function SetPrimaryDatabase($conid) {
        $this->PrimaryDatabase = $conid;
    }

    /**
  	* GetConnection: Returns the Connection Object
  	*
  	* @author     Till Vennefrohne
  	* @version    1.0
  	* @access	  public
  	* @param	  $conid 
  	* @return     MySQL Link
  	*/

    public function GetConnection($conid = false) {
        if (!$conid) $conid = $this->PrimaryDatabase;
        return $this->Connections[$conid];
    }

    /**
  	* Close: Closes an active Database connection
  	*
  	* @author     Till Vennefrohne
  	* @version    1.0
  	* @access	  public
  	* @param	  optional $conid 
  	* @return     None
  	*/

    public function Close($conid = false) {
        if (!$conid) $conid = $this->PrimaryDatabase;
        mysqli_close($this->Connections[$conid]);
    }

    /**
  	* CloseAll: Closes all active Database Connections
  	*
  	* @author     Till Vennefrohne
  	* @version    1.0
  	* @access	  public
  	* @param	  None 
  	* @return     None
  	*/

    public function CloseAll() {
        foreach ($this->Connections as $conid => &$connection) {
            mysqli_close($connection);
            unset($this->Connections[$conid]);
        }
    }

    /**
  	* Escape: Derivat of mysqli_escape_string
  	*
  	* @author     Till Vennefrohne
  	* @version    1.0
  	* @access	  public
  	* @param	  $string, optional $conid 
  	* @return     Escaped $string
  	*/

    public function Escape($str, $conid = false) {
        if (!$conid) $conid = $this->PrimaryDatabase;
        return mysqli_real_escape_string($this->Connections[$conid], $str);
    }

    /**
  	* GetAssoc: Retrieves an associative Array from MySQL query
  	*
  	* @author     Till Vennefrohne
  	* @version    1.0
  	* @access	  public
  	* @param	  $string, optional $conid 
  	* @return     Associative array
  	*/

    public function GetAssoc($query, $conid = false) {
        $ret = array();
        if (!$conid) $conid = $this->PrimaryDatabase;
        $result = mysqli_query($this->Connections[$conid], $query);
        if (!$result) {
            return false;
        } else {
            while($row = mysqli_fetch_assoc($result)) {
                $ret[] = $row;
            }
            return $ret;
        }
    }

    /**
  	* Execute: Executes an SQL Statement on all databases without return value
  	*
  	* @author     Till Vennefrohne
  	* @version    1.0
  	* @access	  public
  	* @param	  $string, optional $conid 
  	* @return     None
  	*/

    public function Execute($query, $conid = false) {
        foreach ($this->Connections as &$connection) {
            $result = mysqli_multi_query($connection, $query);
            while (mysqli_next_result($connection)) {;}     
        }
    }

    /**
  	* Insert: Inserts values from an associative array into a table
  	*
  	* @author     Till Vennefrohne
  	* @version    1.0
  	* @access	  public
  	* @param	  $table, $associativearray 
  	* @return     None
  	*/

    public function Insert($table, $arr) {
        foreach ($this->Connections as &$connection) {
            $fields = array();
            $values = array();
            
            foreach ($arr as $column => $value) {
                $fields[] = mysqli_real_escape_string($connection, $column);
                $values[] = "\"" . mysqli_real_escape_string($connection, $value) . "\"";
            }
      
            $query = 'INSERT INTO ' . mysqli_real_escape_string($connection, $table) . ' (' . implode(' ,', $fields) . ') VALUES (' . implode(' ,', $values) . ')';
    
            $result = mysqli_query($connection, $query);    
        }       
    }

    /**
  	* Update: Updates values from an associative array in a table
  	*
  	* @author     Till Vennefrohne
  	* @version    1.0
  	* @access	  public
  	* @param	  $table, $associativearray, $where 
  	* @return     None
  	*/

    public function Update($table, $arr, $where) {
        foreach ($this->Connections as &$connection) {
            $fields = array();
            
            foreach ($arr as $column => $value) {
                $fields[] = mysqli_real_escape_string($connection, $column) . " = " . "\"" . mysqli_real_escape_string($connection, $value) . "\"";
            }
      
            $query = 'UPDATE ' . mysqli_real_escape_string($connection, $table) . ' SET (' . implode(' ,', $fields) . ') WHERE ' . $where;
            
            $result = mysqli_query($connection, $query);    
        }       
    }

     /**
  	* Count: Returns the line count of a table
  	*
  	* @author     Till Vennefrohne
  	* @version    1.0
  	* @access	  public
  	* @param	  $table, optional $conid 
  	* @return     Integer
  	*/

    public function Count($table, $conid = false) {
        $ret = array();
        if (!$conid) $conid = $this->PrimaryDatabase;
        $result = mysqli_query($this->Connections[$conid], 'SELECT COUNT(*) AS retcntdatafield FROM ' . mysqli_real_escape_string($this->Connections[$conid], $table));
        $row = mysqli_fetch_assoc($result);
        return $row['retcntdatafield'];
    }

}


?>
