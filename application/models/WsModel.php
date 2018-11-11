<?php

class Ws extends BaseModel
{
        public static function generatePassword($password)
            {
                return hash_hmac('sha256', (string)trim($password), Application::getConfig()['salt']);
            }
            
        public function loginws($params = [])
    {
        $s= "tty";//generatePassword("sss");
        return $s;
    }




}