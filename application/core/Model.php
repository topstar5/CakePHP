<?php

class BaseModel
{
    protected $db;
    public $config;

    public function __construct()
    {
        $localFile = '../application/core/Database.local.php';
        $remoteFile = '../application/core/Database.php';
        file_exists($localFile) ? require_once $localFile : require_once $remoteFile;
        $this->db = dbConnect();
        $this->config = Application::getConfig();
    }

    public static function set_object_vars($object, array $vars)
    {
        if(is_array($vars)) {
            $has = get_object_vars($object);

            foreach ($has as $name => $oldValue) {
                $object->$name = isset($vars[$name]) ?
                    htmlspecialchars($vars[$name]) :
                    NULL;
            }
        }else{
            return false;
        }
    }

    public static function fetch(array $vars, $table, $where = [], $options = [], $search = [])
    {
        $sql = 'SELECT ';
        $x = 0;

        foreach($vars as $var){
            $sql .= $var;
            $x != count($vars) - 1 ? $sql .= ', ' : $sql .= ' ';
            $x++;
        }

        $sql .= 'FROM '.$table;

        if($where){
            $x = 0;

            foreach(array_keys($where) as $column){
                $x == 0 ? $sql .= ' WHERE ' : null;

                if ($search == true) {
                    $sql .= $column." LIKE '%$where[$column]%'";
                } else {
                    $where[$column] = "'".$where[$column]."'";
                    $where[$column][0] != '!' ?
                        $sql .= $column.' = '.$where[$column] :
                        $sql .= $column.' != '.$where[$column];
                }

                if($x != count($where) - 1){
                    $search == false ? $sql .= ' AND ' : $sql .= ' OR ';
                }else{
                    $sql .= ' ';
                }

                $x++;
            }
        }

        if($options) {
            foreach ($options as $option) {
                $sql .= ' '.$option.' ';
            }
        }

        $baseModel = new BaseModel();
        $query = $baseModel->db->prepare($sql);
        $query->execute();
        $rsp = $query->fetchAll();
        return $rsp;
    }

    public static function insert($table, array $values)
    {
        $sql = 'INSERT INTO '.$table;
        $execArray = array();
        $x = 0;

        foreach (array_keys($values) as $key) {
            $execArray[':'.$key] = $values[$key];
            $x == 0 ? $sql .= ' (' : null;
            $sql .= $key;
            $x != count($values) - 1 ? $sql .= ', ' : $sql .= ')';
            $x++;
        }

        $sql .= ' VALUES ';
        $x = 0;

        foreach (array_keys($values) as $key) {
            $x == 0 ? $sql .= ' (' : null;
            $sql .= ':'.$key;
            $x != count($values) - 1 ? $sql .= ', ' : $sql .= ')';
            $x++;
        }

        $baseModel = new BaseModel();
        $query = $baseModel->db->prepare($sql);
        $query->execute($execArray);
        return $query->rowCount();
    }

    public static function replaceVar($table, array $vars, $where = [])
    {
        $pairs = array();
        $sql = 'UPDATE '.$table.' ';
        $x = 0;

        foreach (array_keys($vars) as $key) {
            $pairs[':' . $key] = $vars[$key];
            $x == 0 ? $sql .= 'SET ' : $sql .= ', ';
            $sql .= "$key = :$key";
            $x++;
        }

        $x = 0;

        if (count($where) != 0) {
            foreach (array_keys($where) as $key) {
                $x == 0 ? $sql .= ' WHERE ' : $sql .= ' AND ';
                $sql .= "$key = '$where[$key]'";
                $x++;
            }
        }

        $baseModel = new BaseModel();
        $query = $baseModel->db->prepare($sql);
        $query->execute($pairs);
        return $query->rowCount();
    }

    public static function query($query)
    {
        $baseModel = new BaseModel();
        $query = $baseModel->db->prepare($query);
        $query->execute();
        $rsp = $query->fetchAll();
        return $rsp;
    }

    public static function getSettings()
    {
        $baseModel = new BaseModel();
        $query = $baseModel->db->prepare('SELECT * FROM options');
        $query->execute();
        $rsp = $query->fetchAll();
        return $rsp[0];
    }

    public static function getById($tbl, $id)
    {
        $baseModel = new BaseModel();
        $query = $baseModel->db->prepare("SELECT * FROM $tbl WHERE id = '$id' LIMIT 1");
        $query->execute();
        $rsp = $query->fetchAll();

        if(count($rsp[0]) == 0) {
            return false;
        } else {
            return $rsp[0];
        }
    }

    public static function getByMd5Id($tbl, $id)
    {
        $baseModel = new BaseModel();
        $query = $baseModel->db->prepare("SELECT * FROM $tbl WHERE MD5(id) = '$id' LIMIT 1");
        $query->execute();
        $rsp = $query->fetchAll();

        if(count($rsp[0]) == 0) {
            return false;
        } else {
            return $rsp[0];
        }
    }

    public static function getByCode($tbl, $code)
    {
        $baseModel = new BaseModel();
        $query = $baseModel->db->prepare("SELECT * FROM $tbl WHERE code = :code LIMIT 1");
        $query->execute(array(':code' => $code));
        $rsp = $query->fetchAll();
        return $rsp[0];
    }

    public static function getBitcoind()
    {
        $localFile = '../application/core/Bitcoind.local.php';
        $remoteFile = '../application/core/Bitcoind.php';
        file_exists($localFile) ? require_once $localFile : require_once $remoteFile;
        return bitcoind();
    }

    public static function email($data)
    {
        require_once '../application/core/View.php';
        $data['message'] = '<p>'.mailOutput($data['user']->lang, 'HELLO').' '.$data['user']->nickname.',</p><br>'.$data['message'];
        $data['message'] = $data['message'].'<br><p>'.mailOutput($data['user']->lang, 'REGARDS').',<br>'.Application::getConfig()['name'].'</p>';
        $content = View::emailContent($data['message']);
        mail($data['user']->name.' <'.$data['user']->email.'>', $data['subject'], $content['text'], $content['header']);
    }

    public static function upload($uploadedFile, $uploadDir)
    {
        if (is_uploaded_file($uploadedFile['tmp_name'])) {
            $allowedExts = array("gif", "jpeg", "jpg", "png");
            $temp = explode(".", $uploadedFile["name"]);
            $extension = strtolower(end($temp));
            $uploadedFile['name'] = md5(time()).rand(0, 9999).'.'.$extension;

            if((($uploadedFile['size'] <  5120000))
                && (($uploadedFile["type"] == "image/jpeg")
                    || ($uploadedFile["type"] == "image/jpg")
                    || ($uploadedFile["type"] == "image/gif")
                    || ($uploadedFile["type"] == "image/pjpeg")
                    || ($uploadedFile["type"] == "image/x-png")
                    || ($uploadedFile["type"] == "image/png"))
                && in_array($extension, $allowedExts)){
                $target_path = $uploadDir;
                !is_dir($target_path) ? mkdir($target_path, 0777) : null;
                $target_path = $target_path . basename($uploadedFile['name']);
                move_uploaded_file($uploadedFile['tmp_name'], $target_path);
                return $uploadedFile['name'];
            }
        }

        return false;
    }
}