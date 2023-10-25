<?php
class PDOConnect 
{
    private static $instance;
    private $conn;
    private $ServerName;
    private $UID;
    private $PWD;
    private $Db;

    public function __construct($Db)    
    {
        try {
            set_time_limit(900);
            $SQLtxt = file_get_contents('http://localhost/sqldb.txt');
            $items = explode(';', $SQLtxt);
            $this->ServerName = $items[0];
            $this->UID = $items[2];
            $this->PWD = base64_decode($items[3]);
            $this->Db = $Db;
            $this->conn = new PDO("sqlsrv:Server=$this->ServerName;Database=$this->Db", $this->UID, $this->PWD);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
        }
    }

    public static function getInstance($Db)
    {
        if (!self::$instance) {
            self::$instance = new PDOConnect($Db);
        }
        return self::$instance;
    }

    public function select($query, $params = array()) 
    {
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            $stmt= array(
                'rows' => $stmt->fetchAll(PDO::FETCH_ASSOC),
                'count' => $stmt->rowCount()
                       );
            return $stmt;
        } catch(PDOException $e) {
            echo "Error SQL Select: " . $e->getMessage();
        }
    }

    public function insert($table, $data) 
    {
        try {
            $columns = implode(',', array_keys($data));
            $values = ':' . implode(',:', array_keys($data));      
            $query = "INSERT INTO $table ($columns) VALUES ($values)";        

            $stmt = $this->conn->prepare($query);

            foreach ($data as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }

            $stmt->execute();        
            return $stmt->rowCount();
        } catch (PDOException $e) {
            $errorCode = $e->getCode();

            // SQL Server deadlock error code
            if ($errorCode == '40001') {
                // Deadlock occurred, wait and retry
                $retryCount++;
                usleep(1000000); // Wait for 1 second (you can adjust this)
            } else {
                // Other SQL error, re-throw the exception
                throw $e;
            }
        }
    }
    
    public function update($query, $params = array()) 
    {
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            return $stmt->rowCount();
        } catch(PDOException $e) {
            echo "Error SQL Update: " . $e->getMessage();
        }
    }

    public function tempTB($sql,$tableName)
    {
        try {
            $checkTableExists = "IF OBJECT_ID('$tableName', 'U') IS NULL BEGIN $sql END";
    
            $this->conn->exec($checkTableExists);
        } 
        catch (PDOException $e) {
            echo "Chyba při vytváření dočasné tabulky: " . $e->getMessage();
        }
    }
    public function execute($query, $params = array()) 
    {
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
        } catch(PDOException $e) {
            echo "Error SQL Select: " . $e->getMessage();
        }
    }
}

// FTP downloader
class FTP
{    
    private $FTPId;
    private $FTPServer;
    private $UID;
    private $PWD;
    private $remotefilepath;

    public function __construct($FTPfile)    
    {
        try {
            $SQLtxt = file_get_contents("http://localhost/$FTPfile");
            $items = explode(';', $SQLtxt);
            $this->FTPServer = $items[0];
            $this->UID = $items[1];
            $this->PWD = base64_decode($items[2]);            
            $this->remotefilepath = $items[3];
        } catch(Exception $e)  {
            echo "Connection failed: " . $e->getMessage();
        }
    }

    public function FTP_download($localFilePath, $filename)
    {
        $proxyParams = ProxyParameters::getParameters();
        
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_PROXY, $proxyParams['proxy']['http']);
        curl_setopt($curl, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
        
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, $proxyParams['ssl']['verify_peer']);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, $proxyParams['ssl']['verify_peer_name']);
        $remotefilepath=$this->remotefilepath.$filename;

        curl_setopt($curl, CURLOPT_URL, "ftp://{$this->UID}:{$this->PWD}@{$this->FTPServer}{$remotefilepath}");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_FILE, fopen($localFilePath . $filename, 'w'));

        $result = curl_exec($curl);

        if ($result === false) {
            throw new Exception(curl_error($curl));
        }
        curl_close($curl);
    }

    public function getRemoteFileCreationTime($filename)
    {
        $connId = ftp_connect($this->FTPServer);
        $login = ftp_login($connId, $this->UID, $this->PWD);

        if ($connId && $login) {
            $fileTimestamp = ftp_mdtm($connId, $this->remotefilepath.$filename);

            if ($fileTimestamp != -1) {
                $createdDate = date('Y-m-d H:i:s', $fileTimestamp);
                return $createdDate;
            } else {
                return "Nepodařilo se získat čas vytvoření souboru.";
            }

            ftp_close($connId);
        } else {
            return "Nepodařilo se připojit k FTP serveru.";
        }
    }    
}


class ProxyParameters
{
    public static function getParameters()
    {
        $Proxytxt = file_get_contents('http://localhost/proxy.txt');
        $items = explode(';', $Proxytxt);
        return [
            'proxy' => [
                'http' => "http://".$items[0].":".$item[1],
                'ssl' => "http://".$items[0].":".$item[1]
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ]
        ];
    }
}
?>