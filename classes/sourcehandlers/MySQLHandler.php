<?php

class MySQLHandler extends SourceHandler
{
    var $m_sContentClassName = 'eZ Content Class';
    var $handlerTitle = 'Abstract MySQL Handler';
    var $mysqli = false;
    var $current_row_hash = false;
    var $current_row_index = false;
    var $mapping = false;
    var $first_field = false;
    var $offset;
    var $limit;
    var $host;
    var $username;
    var $password;
    var $dbname;
    var $table;
    var $port;
    var $socket;

    function __construct( $offset, $limit, $host, $username, $password, $dbname, $table, $port = 3306, $socket = '' )
    {
        $this->offset = $offset;
        $this->limit = $limit;
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
        $this->dbname = $dbname;
        $this->table = $table;
        $this->port = $port;
        $this->socket = $socket;
    }

    public function __destruct()
    {
        if ( is_object( $this->mysqli ) and
             count( get_class_vars( get_class( $this->mysqli ) ) ) > 0 )
        {
            /* close connection */
            $this->mysqli->close();
        }
    }


    /*
     * init the data, read from file, etc.
     */
    function readData()
    {
        $this->current_row = false;
        $this->current_row_hash = false;
        $this->current_row_index = $this->offset - 1;
        $this->mapping = false;

        $this->mysqli = new mysqli( $this->host, $this->username, $this->password,
                                    $this->dbname, $this->port, $this->socket );

        /* check connection */
        if ( mysqli_connect_errno() )
        {
            printf( "Connect failed: %s\n", mysqli_connect_error() );
            exit();
        }
    }

    /*
     * the ezp target-class name
     */
    function getTargetContentClass()
    {
        return $this->m_sContentClassName;
    }

    /*
     * return external index of data row
     */
    function getDataRowId()
    {
        return $this->idPrepend . $this->current_row_hash['id'];
    }

    function getParentNodeId()
    {
        return 2;
    }

    /*
     * Get next row from the table
     */
    function getNextRow()
    {
        $this->current_row_index++;
        if ( $this->current_row_index > ( $this->offset + $this->limit - 1 ) )
        {
            $this->current_row = false;
            $this->current_row_hash = false;
            $this->mapping = false;
            return $this->current_row;
        }

        $result = $this->mysqli->query( 'SELECT * FROM ' . $this->table .
                                        ' LIMIT 1 OFFSET ' . $this->current_row_index );
        if ( $result->num_rows == 1 )
        {
            $this->current_row = $result->fetch_row();
            $this->current_row_hash = array();
            $this->mapping = array();
            foreach ( $result->fetch_fields() as $key => $field )
            {
                $this->current_row_hash[$field->name] = $this->current_row[$key];
                $this->mapping[$key] = $field->name;
            }
            $this->current_field = current( $this->mapping );
        }
        else
        {
            $this->current_row = false;
            $this->current_row_hash = false;
            $this->mapping = false;
        }
        $result->free();

        return $this->current_row;
    }

    /*
     * use php array pointers
     */
    function getNextField()
    {
        if ( $this->first_field )
        {
            $this->first_field = false;
        }
        else
        {
            next( $this->mapping ); // nirvana
        }

        $this->current_field = current( $this->mapping );
        return $this->current_field;
    }

    /*
     * return the content-class attribute-name
     */
    function geteZAttributeIdentifierFromField()
    {
        $this->current_field = current( $this->mapping );
        return $this->current_field;
    }

    /*
     * return the value for the current field
     */
    function getValueFromField()
    {
        return $this->current_row[ key( $this->mapping ) ];
    }

}
?>
