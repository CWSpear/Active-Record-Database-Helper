<?php
class CamDB extends MySQLi
{
    private $_type = 'select';

    private $_result   = array();
    private $_mysqli_result = array();
    
    private $_update   = array();
    private $_insert   = array();
    private $_select   = array();
    private $_table    = array();
    private $_join     = array();
    private $_where    = array();
    private $_or_where = array();
    private $_group_by = array();
    private $_order_by = array();
    private $_limit    = '';

    public $test_mode = false;

    // test mode
    public function __construct($test_mode = false)
    {
        // don't run MySQLi constructor if we're just testing CamDB functionality
        if($test_mode !== true && $test_mode !== 'test')
        {
            parent::__construct();
        }
        else
        {
            $this->test_mode = true;
        }
    }
    
    public function select($select, $table = '')
    {
        if(!empty($table)) $this->table($table);
        
        if(!is_array($select))
        {
            $select = explode(',', $select);
            foreach($select as &$s) $s = trim($s);
        }
        $this->_select = array_merge($select, $this->_select);
        $this->_type = 'select';
        return $this; // for chaining
    }
        
    // this function triggers a query
    public function insert($insert, $table = '')
    {
        if(!empty($table)) $this->table($table);
        
        if(!is_array($insert))
        {
            $insert = explode(',', $insert);
        }
        foreach($insert as &$i) $i = $this->_prep_value(trim($i));

        $this->_insert = array_merge($insert, $this->_insert);
        $this->_type = 'insert';

        $query = $this->fetch_query();
        $result = $this->query($query);

        $this->reset();
        return $result;
    }
    
    // this function triggers a query
    public function update($update, $table = '')
    {
        if(!empty($table)) $this->table($table);
        
        if(!is_array($update))
        {
            $update = explode(',', $update);
        }
        foreach($update as &$u) $u = $this->_prep_value(trim($u));
        
        $this->_update = array_merge($update, $this->_update);
        $this->_type = 'update';

        $query = $this->fetch_query();
        $result = $this->query($query);

        $this->reset();
        return $result;
    }
    
    // this function triggers a query
    public function delete($table = '', $where = '')
    {
        if(!empty($table)) $this->table($table);
        if(!empty($where)) $this->where($where);
        
        $this->_type = 'delete';

        $query = $this->fetch_query();
        $result = $this->query($query);

        $this->reset();
        return $result;
    }
            
    public function table($table)
    {
        if(!is_array($table))
        {
            $table = explode(',', $table);
            foreach($table as &$t) $t = trim($t);
        }
        $this->_table = array_merge($table, $this->_table);
        return $this; // for chaining
    }

    // alias for table
    public function from($table)
    {
        $this->table($table);
        return $this;
    }
    
    public function where($key, $value = '')
    {
        if(!is_array($key)) $key = array($key => $value);
        
        foreach($key as $k => $v)
        {
            $this->_where[$k] = $v;
        }

        return $this; // for chaining
    }
    
    public function or_where($key, $value = '')
    {
        die('or_where is NYI');
        if(!is_array($key) && !is_array($value)) $key = array($key => $value);
        else if(!is_array($key) && is_array($value))
        {
            foreach($value as $v)
            {
                $this->_or_where[$key][] = $v;
            }
        }
        else
        {
            foreach($key as $k => $v)
            {
                $this->_or_where[$k][] = $v;
            }
        }
        return $this; // for chaining
    }
    
    public function in($key, $values)
    {
        $this->_where[$key] = $values;
        return $this; // for chaining
    }
    
    public function join($table, $on = '')
    {
        if(!is_array($table)) $table = array($table => $on);
        
        foreach($table as $k => $v)
        {
            $this->_join[$k] = $v;
        }
        return $this; // for chaining
    }
    
    public function group_by($group_by)
    {
        if(!is_array($group_by))
        {
            $group_by = explode(',', $group_by);
            foreach($group_by as &$s) $s = trim($s);
        }
        $this->_group_by = array_merge($group_by, $this->_group_by);
        return $this; // for chaining
    }
    
    public function order_by($item, $dir = '')
    {
        if(!is_array($item)) $item = array($item => $dir);
        
        foreach($item as $i => $d)
        {
            $this->_order_by[$i] = empty($d) ? 'ASC' : $d;
        }
        return $this; // for chaining
    }
    
    public function limit($limit)
    {
        $this->_limit = $limit;
        return $this; // for chaining
    }
    
    
    // private functions to build query
    private function _insert()
    {
        return 'INSERT INTO ';
    }
    
    private function _select()
    {
        if(empty($this->_select)) $this->_select = array('*');
        return 'SELECT ' . implode(', ', $this->_select);
    }
    
    private function _update()
    {
        return 'UPDATE';
    }
    
    private function _set()
    {
        if($this->_type == 'update')
        {
            $set = array();
            foreach($this->_update as $key => $value)
            {
                $set[] = "{$key} = '{$value}'";
            }
            return empty($set) ? '' : 'SET ' . implode(', ', $set);
        }        
        
        if($this->_type == 'insert')
        {
            $fields = array_keys($this->_insert);
            $values = array_values($this->_insert);
            return '(' . implode(', ', $fields) . ') VALUES (\'' . implode("', '", $values) . '\')';
        }
        
        return '';
    }
    
    private function _delete()
    {
        return 'DELETE';
    }

    private function _table()
    {
        $str = $this->_type != 'insert' && $this->_type != 'update' ? 'FROM ' : '';
        return  $str . implode(', ', $this->_table);
    }

    private function _join()
    {
        if($this->_type != 'select') return '';
            
        $join = array();
        foreach($this->_join as $table => $on)
        {
            $join[] = "JOIN {$table} ON {$on}";
        }
        return implode(' ', $join);
    }

    private function _where()
    {
        $where = array();
        foreach($this->_where as $key => $value)
        {
            if(!empty($key))
            {
                if(!is_array($value))
                {
                    // key can supply its own operand, namely >, <, >=, <=
                    $operand = '=';
                    preg_match('/ ([><!]=?)$/', $key, $match);
                    if(!empty($match[1]))
                    {
                        // I could just make $operand be '', but someday, I may escape the field
                        $key = preg_replace('/ ([><!]=?)$/', '', $key);
                        $operand = $match[1];
                    }

                    $value = $this->_prep_value($value);

                    $where[] = "{$key} {$operand} {$value}";
                }
                else
                {
                    // foreach($value as &$v)
                    // {
                    //     $v = $this->_prep_value($v);
                    //     $where[] = "{$key} = '{$v}'";
                    // }

                    foreach($value as &$v) $v = $this->_prep_value($v);
                    $value = implode(", ", $value);
                    $where[] = "{$key} IN ({$value})";
                }
            }
        }
        return empty($where) ? '' : 'WHERE ' . implode(' AND ', $where);
    }
    
    private function _order_by()
    {
        if($this->_type != 'select') return '';
            
        $order_by = array();
        foreach($this->_order_by as $item => $dir)
        {
            $order_by[] = "{$item} {$dir}";
        }
        return empty($order_by) ? '' : 'ORDER BY ' . implode(', ', $order_by);
    }
    
    private function _group_by()
    {
        if(empty($this->_group_by) || $this->_type != 'select') return '';
        return 'GROUP BY ' . implode(', ', $this->_group_by);
    }
    
    private function _limit()
    {
        if($this->_type != 'select') return '';
        
        return empty($this->_limit) ? '' : 'LIMIT ' . $this->_limit;
    }

    private function _prep_value($value)
    {
        echo "$value", gettype($value);
        switch(gettype($value))
        {
            case 'boolean':
            case 'string':
                if($this->test_mode) $return = mysql_real_escape_string($value);
                $return = $this->real_escape_string($value);
                return "'{$return}'";

            case 'integer':
            case 'double':
                return $value;

            case 'array':
            case 'object':
            case 'resource':
            case 'NULL':
            case 'unknown type':
                die('Error... a value has the type "' . gettype($value) . '" which is not valid');
        }
    }
    
    public function fetch_query($reset = true)
    {
        $query[] = $this->{'_' . $this->_type}();
        $query[] = $this->_table();
        $query[] = $this->_join();
        $query[] = $this->_set();
        $query[] = $this->_where();
        $query[] = $this->_group_by();
        $query[] = $this->_order_by();
        $query[] = $this->_limit();
                
        return implode(' ', $query);
    }

    public function query($query)
    {
        return $this->_mysqli_result = parent::query($query);
    }

    public function result($reset = true)
    {
        $query = $this->fetch_query();
        $mysqli_result = $this->query($query);

        $this->_result = array();
        while($row = $mysqli_result->fetch_object())
        {
            $this->_result[] = $row;
        }

        $result = $this->_result;
        if($reset) $this->reset();
        return $result;
    }

    public function result_array($reset = true)
    {
        $rows = $this->result($reset);
        foreach($rows as &$row) $row = (array) $row;

        return $rows;
    }

    public function row($reset = true)
    {
        $query = $this->limit(1)->fetch_query();
        $mysqli_result = $this->query($query);

        $this->_result = $mysqli_result->fetch_object();

        $result = $this->_result;
        if($reset) $this->reset();
        return $result;
    }

    public function row_array($reset = true)
    {
        return (array) $this->row($reset);
    }
    
    public function reset()
    {
        $this->_type     = 'select';
        $this->_result   = array();

        $this->_update   = array();
        $this->_insert   = array();
        $this->_select   = array();
        $this->_table    = array();
        $this->_join     = array();
        $this->_where    = array();
        $this->_or_where = array();
        $this->_group_by = array();
        $this->_order_by = array();
        $this->_limit    = '';
    }
}