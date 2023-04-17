<?php

class DBController {

	private $host = "localhost";
	private $user = "root";
	private $password = "root";
	private $database = "balance";
	private $conn;
	
    function __construct() {
        $this->conn = $this->connectDB();
	}	
	
	function connectDB() {
		$conn = mysqli_connect($this->host,$this->user,$this->password,$this->database);
		return $conn;
	}
	
    function runBaseQuery($query) {
        $result = mysqli_query($this->conn,$query);
        while($row=mysqli_fetch_assoc($result)) {
            $resultset[] = $row;
        }		
        if(!empty($resultset))
            return $resultset;
    }
    
    function runQuery($query, $param_type, $param_value_array) {
        $sql = $this->conn->prepare($query);
        $this->bindQueryParams($sql, $param_type, $param_value_array);
        $sql->execute();
        $result = $sql->get_result();
        
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $resultset[] = $row;
            }
        }
        
        if(!empty($resultset)) {
            return $resultset;
        }
    }
    
    function bindQueryParams($sql, $param_type, $param_value_array) {
        $param_value_reference[] = & $param_type;
        for($i=0; $i<count($param_value_array); $i++) {
            $param_value_reference[] = & $param_value_array[$i];
        }
        call_user_func_array(array(
            $sql,
            'bind_param'
        ), $param_value_reference);
    }
    
    function insert($query, $param_type, $param_value_array, $table = null) {
        $sql = $this->conn->prepare($query);
        $this->bindQueryParams($sql, $param_type, $param_value_array);
        if($sql->execute()){
            if(!empty($table)){
                $objeto = $this->runBaseQuery('select * from '.$table.' where id = '.mysqli_insert_id($this->conn));
                if(!empty($objeto)){
                    return $objeto[0];
                }
            }
            return true;
        }
        return $sql->error;
    }
    
    function update($query, $param_type, $param_value_array, $table = null, $id = null) {
        $sql = $this->conn->prepare($query);
        $this->bindQueryParams($sql, $param_type, $param_value_array);
        if($sql->execute()){
            if(!empty($table)){
                $objeto = $this->runBaseQuery('select * from '.$table.' where id = '.$id);
                if(!empty($objeto)){
                    return $objeto[0];
                }
            }
            return true;
        }
        return $sql->error;
    }

    function delete($query, $param_type, $param_value_array) {
        $sql = $this->conn->prepare($query);
        $this->bindQueryParams($sql, $param_type, $param_value_array);
        return $sql->execute();
    }

}
?>