<?php

/***************************************************************************
 *
 * Nephthys - file sharing management
 * Copyright (c) by Andreas Unterkircher, unki@netshadow.at
 *
 *  This file is part of Nephthys.
 *
 *  Nephthys is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  Nephthys is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with Nephthys. If not, see <http://www.gnu.org/licenses/>.
 *
 ***************************************************************************/

class NEPHTHYS_DB {

   private $db;
   private $cfg;
   private $is_connected;
   private $last_error;
   private $parent;

   /**
    * NEPHTHYS_DB class constructor
    *
    * This constructor initially connect to the database.
    */
   public function __construct()
   {
      global $nephthys;
      $this->parent =& $nephthys;

      $this->cfg = $this->parent->cfg;

      /* We are starting disconnected */
      $this->setConnStatus(false);

      /* Connect to MySQL Database */
      $this->db_connect();

   } // __construct()
	 
   /**
    * NEPHTHYS_DB class deconstructor
    *
    * This destructor will close the current database connection.
    */ 
   public function __destruct()
   {
      $this->db_disconnect();

   } // _destruct()

   /**
    * NEPHTHYS_DB database connect
    *
    * This function will connect to the database via MDB2
    */
   private function db_connect()
   {
      $options = array(
         'debug' => 2,
         'portability' => 'DB_PORTABILITY_ALL'
      );

      switch($this->cfg->db_type) {
         default:
         case 'mysql':
            $dsn = "mysqli://"
               . $this->cfg->mysql_user .":"
               . $this->cfg->mysql_pass ."@"
               . $this->cfg->mysql_host ."/"
               . $this->cfg->mysql_db;
            break;
         case 'sqlite':
            $dsn = "sqlite:///"
               . $this->cfg->sqlite_path;
            break;

      }

      $this->db = MDB2::connect($dsn, $options);

      if(PEAR::isError($this->db)) {
         $this->throwError("Unable to connect to database: ". $this->db->getMessage() .' - '. $this->db->getUserInfo());
         $this->setConnStatus(false);
      }

      $this->setConnStatus(true);

   } // db_connect()

   /**
    * NEPHTHYS_DB database disconnect
    *
    * This function will disconnected an established database connection.
    */
   private function db_disconnect()
   {
      $this->db->disconnect();

   } // db_disconnect()

   /**
    * NEPHTHYS_DB database query
    *
    * This function will execute a SQL query and return the result as
    * object.
    */
   public function db_query($query = "", $mode = MDB2_FETCHMODE_OBJECT)
   {
      if($this->getConnStatus()) {

         $this->db->setFetchMode($mode);

         /* for manipulating queries use exec instead of query. can save
          * some resource because nothing has to be allocated for results.
          */
         if(preg_match('/^(update|insert)i/', $query)) {
            $result = $this->db->exec($query);
         }
         else {
            $result = $this->db->query($query);
         }
			
         if(PEAR::isError($result))
            $this->throwError($result->getMessage() .' - '. $result->getUserInfo());
	
         return $result;
      }
      else 
         $this->throwError("Can't execute query - we are not connected!");

   } // db_query()

   /**
    * NEPHTHYS_DB database prepare query
    *
    * This function will prepare a SQL query to be executed
    * @param string $query
    * @param int $mode
    * @return mixed
    */
   public function db_prepare($query = "")
   {
      if($this->getConnStatus()) {

         $this->db->prepare($query);

         /* for manipulating queries use exec instead of query. can save
          * some resource because nothing has to be allocated for results.
          */
         if(preg_match('/^(update|insert|delete)i/', $query)) {
            $sth = $this->db->prepare($query, MDB2_PREPARE_MANIP);
         }
         else {
            $sth = $this->db->prepare($query, MDB2_PREPARE_RESULT);
         }

         if(PEAR::isError($sth))
            $this->throwError($sth->getMessage() .' - '. $sth->getUserInfo());

         return $sth;
      }
      else
         $this->throwError("Can't prepare query - we are not connected!");

   } // db_prepare()

   /**
    * NEPHTHYS_DB database execute a prepared query
    *
    * This function will execute a previously prepared SQL query
    * @param mixed $sth
    * @param mixed $data
    */
   public function db_execute($sth, $data)
   {
      if($this->getConnStatus()) {

         $result = $sth->execute($data);

         if(PEAR::isError($result))
            $this->throwError($result->getMessage() .' - '. $result->getUserInfo());

      }
      else
         $this->throwError("Can't prepare query - we are not connected!");

   } // db_execute()

   /**
    * NEPHTHYS_DB database query & execute
    *
    * This function will execute a SQL query and return nothing.
    */
   public function db_exec($query = "")
   {
      if(!$this->getConnStatus())
         return false;

      $affected =& $this->db->exec($query);

      if(PEAR::isError($affected)) {
         $this->throwError($affected->getMessage());
         return false;
      }

      return true;

   } // db_exec()

   /**
    * NEPHTHYS_DB fetch ONE row
    *
    * This function will execute the given but only return the
    * first result.
    */
   public function db_fetchSingleRow($query = "", $mode = MDB2_FETCHMODE_OBJECT)
   {
      if($this->getConnStatus()) {

         $row = $this->db->queryRow($query, array(), $mode);

         if(PEAR::isError($row))
            $this->throwError($row->getMessage() .' - '. $row->getUserInfo());

         return $row;
	
      }
      else {
   
         $this->throwError("Can't fetch row - we are not connected!");
      
      }
      
   } // db_fetchSingleRow()

   /**
    * NEPHTHYS_DB number of affected rows
    *
    * This functions returns the number of affected rows but the
    * given SQL query.
    */
   public function db_getNumRows($query = "")
   {
      /* Execute query */
      $result = $this->db_query($query);

      /* Errors? */
      if(PEAR::isError($result))
         $this->throwError($result->getMessage() .' - '. $result->getUserInfo());

      return $result->numRows();

   } // db_getNumRows()

   /**
    * NEPHTHYS_DB get primary key
    *
    * This function returns the primary key of the last
    * operated insert SQL query.
    */
   public function db_getid($table_name = null)
   {
      /* Get the last primary key ID from execute query */
      $id = $this->db->lastInsertID($table_name);
      if (PEAR::isError($id)) {
          $this->throwError($id->getMessage());
      }

      return $id;
      
   } // db_getid()

   /**
    * NEPHTHYS_DB check table exists
    *
    * This function checks if the given table exists in the
    * database
    * @param string, table name
    * @return true if table found otherwise false
    */
   public function db_check_table_exists($table_name = "")
   {
      if(!$this->getConnStatus())
         return false;

      switch($this->cfg->db_type) {
         default:
         case 'mysql':
            $result = $this->db_query("SHOW TABLES");
            $tables_in = "Tables_in_". $this->cfg->mysql_db;
            while($row = $result->fetchRow()) {
               if($row->$tables_in == $table_name)
                  return true;
            }
            break;
         case 'sqlite':
            $result = $this->db_query("SELECT name FROM sqlite_master WHERE type='table'");
            while($row = $result->fetchRow()) {
               if($row->name == $table_name)
                  return true;
            }
            break;
      }

      return false;
	 
   } // db_check_table_exists()

   /**
    * NEPHTHYS_DB rename table
    * 
    * This function will rename an database table
    * @param old_name, new_name
    */
   public function db_rename_table($old, $new)
   {
      if($this->db_check_table_exists($old)) {
         if(!$this->db_check_table_exists($new))
            $this->db_query("RENAME TABLE ". $old ." TO ". $new);
         else
            $this->throwError("Can't rename table ". $old ." - ". $new ." already exists!");
      }
	 
   } // db_rename_table()

   /**
    * NEPHTHYS_DB drop table
    *
    * This function will delete the given table from database
    */
   public function db_drop_table($table_name)
   {
      if($this->db_check_table_exists($table_name))
         $this->db_query("DROP TABLE ". $table_name);

   } // db_drop_table()

   /**
    * NEPHTHYS_DB truncate table
    *
    * This function will truncate (reset) the given table
    */
   public function db_truncate_table($table_name)
   {
      if($this->db_check_table_exists($table_name)) 
         $this->db_query("TRUNCATE TABLE ". $table_name);

   } // db_truncate_table()

   /**
    * NEPHTHYS_DB check column exist
    *
    * This function checks if the given column exists within
    * the specified table.
    */
   public function db_check_column_exists($table_name, $column)
   {
      if(!$this->getConnStatus())
         return false;

      switch($this->cfg->db_type) {
         default:
         case 'mysql':
            $result = $this->db_query("DESC ". $table_name, MDB2_FETCHMODE_ORDERED);
            while($row = $result->fetchRow()) {
            if(in_array($column, $row))
               return true;
            }
            break;
         case 'sqlite':
            $result = $this->db_query("
               SELECT sql
               FROM
                  (SELECT * FROM sqlite_master UNION ALL
                   SELECT * FROM sqlite_temp_master)
               WHERE
                  tbl_name LIKE '". $table_name ."'
               AND type!='meta'
               AND sql NOT NULL
               AND name NOT LIKE 'sqlite_%'
               ORDER BY substr(type,2,1), name
            ");
            while($row = $result->fetchRow()) {
               /* CREATE TABLE xx ( col1 int, col2 bool, col3 ...) */
               if(strstr($row->sql, " ". $column ." ") !== false)
                  return true;
            }
            break;
      }

      return false;

   } // db_check_column_exists()

   /**
    * NEPHTHYS_DB check index exists
    *
    * This function checks if the given index can be found
    * within the specified table.
    */
   public function db_check_index_exists($table_name, $index_name)
   {
      $result = $this->db_query("DESC ". $table_name, MDB2_FETCHMODE_ORDERED);

      while($row = $result->fetchRow()) {
         if(in_array("KEY `". $index_name ."`", $row))
            return 1;
      }

      return 0;

   } // db_check_index_exists()

   /**
    * NEPHTHYS_DB alter table
    *
    * This function offers multiple methods to alter a table.
    * * add/modify/delete columns
    * * drop index
    */
   public function db_alter_table($table_name, $option, $column, $param1 = "", $param2 = "")
   {
      if(!$this->db_check_table_exists($table_name)) {
         $this->throwError("Table ". $table_name ." does not exist!");
         return false;
      }

      switch($this->cfg->db_type) {
         default:
         case 'mysql':

            switch(strtolower($option)) {

               case 'add':
                  if(!$this->db_check_column_exists($table_name, $column))
                     $this->db_query("ALTER TABLE ". $table_name ." ADD ". $column ." ". $param1);
                  break;

               case 'change':
                  if($this->db_check_column_exists($table_name, $column))
                     $this->db_query("ALTER TABLE ". $table_name ." CHANGE ". $column ." ". $param1);
                  break;

               case 'drop':
                  if($this->db_check_column_exists($table_name, $column))
                     $this->db_query("ALTER TABLE ". $table_name ." DROP ". $column);
                  break;

               case 'dropidx':
                  if($this->db_check_index_exists($table_name, $column))
                     $this->db_query("ALTER TABLE ". $table_name ." DROP INDEX ". $column);
                  break;

            }
            break;

         case 'sqlite':

            $this->throwError("SQLite only support ALTER TABLE rudimentary with version 3, so no support here right now.");
            break;

      }

   } // db_alter_table()

   /**
    * NEPHTHYS_DB get database version
    *
    * This functions returns the current database version
    */
   public function getVersion()
   {
      if($this->db_check_table_exists(MYSQL_PREFIX ."settings")) {
         $result = $this->db_fetchSingleRow("
            SELECT setting_value 
            FROM ". MYSQL_PREFIX ."settings 
            WHERE setting_key LIKE 'version'
         ");
         return $result->setting_value;
      }
      else
         return 0;
	 
   } // getVersion()

   /**
    * NEPHTHYS_DB set version
    *
    * This function sets the version of database
    */
   public function setVersion($version)
   {
      $this->db_query("
         REPLACE INTO ". MYSQL_PREFIX ."settings 
            (setting_key, setting_value)
         VALUES ('version', '". $version ."')
      ");
      
   } // setVersion()

   /**
    * NEPHTHYS_DB get connection status
    *
    * This function checks the internal state variable if already
    * connected to database.
    */
   private function setConnStatus($status)
   {
      $this->is_connected = $status;
      
   } // setConnStatus()

   /**
    * NEPHTHYS_DB set connection status
    * This function sets the internal state variable to indicate
    * current database connection status.
    */
   private function getConnStatus()
   {
      return $this->is_connected;

   } // getConnStatus()

   /**
    * NEPHTHYS_DB throw error
    *
    * This function shows up error messages and afterwards through exceptions.
    */
   private function throwError($string)
   {
      if(!defined('DB_NOERROR'))  {

         $this->parent->_error($string);

         try {
            throw new NEPHTHYS_EXCEPTION;
         }
         catch(NEPHTHYS_EXCEPTION $e) {
            print "<br /><br />\n";
            $this->parent->_error($e);
            die;
         }
      }

      $this->last_error = $string;
	 
   } // throwError()

   /**
    * quote string
    *
    * this function handsover the provided string to the MDB2
    * quote() function which will return the, for the selected
    * database system, correctly quoted string.
    *
    * @param string $query
    * @return string
    */
   public function db_quote($obj)
   {
      return $this->db->quote($obj);

   } // db_quote()

   /**
    * start transaction
    *
    * this will start a transaction on ACID-supporting database
    * systems.
    *
    * @return bool
    */
   public function db_start_transaction()
   {
      if(!$this->getConnStatus())
         return false;

      if(!$this->db->supports('transactions'))
         return false;

      $result = $this->db->beginTransaction();

      /* Errors? */
      if(PEAR::isError($result))
         $this->throwError($result->getMessage() .' - '. $result->getUserInfo());

      return true;

   } // db_start_transaction()

   /**
    * commit transaction
    *
    * this will commit an ongoing transaction on ACID-supporting
    * database systems
    *
    * @return bool
    */
   public function db_commit_transaction()
   {
      if(!$this->getConnStatus())
         return false;

      if(!$this->db->inTransaction())
         return false;

      $result = $this->db->commit();

      /* Errors? */
      if(PEAR::isError($result))
         $this->throwError($result->getMessage() .' - '. $result->getUserInfo());

      return true;

   } // db_commit_transaction()

   /**
    * rollback transaction()
    *
    * this function aborts a on going transaction
    *
    * @return bool
    */
   public function db_rollback_transaction()
   {
      if(!$this->getConnStatus())
         return false;

      if(!$this->db->inTransaction())
         return false;

      $result = $this->db->rollback();

      /* Errors? */
      if(PEAR::isError($result))
         $this->throwError($result->getMessage() .' - '. $result->getUserInfo());

      return true;

   } // db_rollback_transaction()

} // NEPHTHYS_DB()

// vim: set filetype=php expandtab softtabstop=3 tabstop=3 shiftwidth=3 autoindent smartindent:
?>
