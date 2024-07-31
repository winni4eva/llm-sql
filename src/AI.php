<?php 
namespace Winnipass\AiSql;

use PDO;

class AI
{


    public function __construct($conn, $options = [])
    {
        $this->conn = $conn;
        $this->options = $options;
    }

}