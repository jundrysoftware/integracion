<?php
class DB{
    private $host;
    private $db;
    private $user;
    private $password;
    private $charset;

    public function __construct(){
        $this->host     = 'db.ceiba.extreme.com.co';
        $this->port     = '5432';
        $this->db       = 'kaguaprod';
        $this->user     = 'infraestructura';
        $this->password = "[rXw:upQ}EII7";
    }

    function connect(){
        try{
            $connection = "pgsql:host=".$this->host.";port=".$this->port.";dbname=" . $this->db;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            $pdo = new PDO($connection,$this->user,$this->password);
            return $pdo;
        }catch(PDOException $e){
            print_r('Error connection: ' . $e->getMessage());
        }   
    }
}
?>