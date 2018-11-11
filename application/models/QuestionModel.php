<?php

require_once 'AnswerModel.php';

class Question extends BaseModel
{
    public $id;
    public $text;
    public $descr;
    public $tags;
    public $reward;
    public $user_id;
    public $created;
    public $file;
    public $closed;
    public $answers;
    public $user;
    public $email_sent;
    public $question_lang;
    public $category;

    public function __construct($id)
    {
        $query = BaseModel::fetch(array('*'), 'questions', array('id' => $id), array('LIMIT 1'));

        if (count($query) != 0) {
            $this->set_object_vars($this, $query[0]);
            $this->answers = $this->getAnswers();
            $this->user = $this->getUser();
            $this->tags = $this->getTags();
            !empty($this->file) ? $filename = explode('.', $this->file)[0] : $filename = false;

            if ($filename && file_exists('uploads/questions/'.$this->file) && !file_exists('uploads/questions/'.$filename.'_thumb.jpg')) {
                $image = \PHPImageWorkshop\ImageWorkshop::initFromPath('uploads/questions/'.$this->file);
                $image->resizeInPixel(100, 75, true);
                $image->save('uploads/questions/', $filename.'_thumb.jpg', false, null, 75);
            }

            return $this;
        } else {
            return false;
        }
    }

    public static function getByMd5($questionId_md5)
    {
        $question = BaseModel::getByMd5Id('questions', $questionId_md5);

        if ($question) {
            $question = new Question($question['id']);
        } else {
            $question = false;
        }

        return $question;
    }

    public static function getByTag($tag)
    {
        $questions = array();
        $query = BaseModel::query("SELECT id FROM questions WHERE tags LIKE '%$tag%' ORDER BY created DESC");

        if ($query && count($query) != 0) {
            foreach ($query as $row) {
                $questions[] = new Question($row['id']);
            }
        }

        return $questions;
    }

    public function getUser()
    {
        $user = new User($this->user_id);
        return $user;
    }

    public function thumbnail()
    {
        $dir = internalLink('uploads/questions/');

        if (!empty($this->file) && file_exists('uploads/questions/'.$this->file)) {
            $filename = explode('.', $this->file)[0];
            return $dir.$filename.'_thumb.jpg';
        } else {
            $dir = internalLink('img/');
            return $dir.'default_thumb.jpg';
        }
    }

    public function hasBeenAnsweredBy($userObj)
    {
        $query = BaseModel::fetch(array('id'), 'answers', array('question_id' => $this->id, 'user_id' => $userObj->id));
        return count($query) > 0;
    }

    public function getAnswers()
    {
        $answers = array();
        $query = BaseModel::query('SELECT id FROM answers WHERE question_id = '.$this->id.' AND answer_id = ""');

        if (count($query) != 0) {

            foreach ($query as $row) {
                $answers[] = new Answer($row['id']);
            }
        }

        return $answers;
    }

    public function getTags()
    {
        $tags = array();

        if (!empty($this->tags)) {
            $split = explode(',', $this->tags);

            if (count($split) != 0) {
                foreach ($split as $tag) {
                    $tags[] = trim($tag);
                }
            }
        }

        return $tags;
    }

    public function answer($userObj, $answerText)
    {
        if (!$this->hasBeenAnsweredBy($userObj)) {
            $vals = array(
                'text' => $answerText,
                'user_id' => $userObj->id,
                'created' => now(),
                'question_id' => $this->id
            );

            $query = BaseModel::insert('answers', $vals);

            $data['text'] = $vals['text'];
            $data['hashed_id'] = md5($vals['question_id']);

            if ($query) {
                $question_writer = BaseModel::fetch(array('id','user_id'), 'questions', array('id' => $vals['question_id']));
                foreach ($question_writer as $question) {
                    $owner_id = $question['user_id'];
                    $notification = BaseModel::fetch(array('id', 'answer_inquiry_email'), 'users', array('id' => $owner_id));
                    foreach ($notification as $user) {
                        if ($user['answer_inquiry_email'] == 1) {
                            $user = new User($user['id']);
                            BaseModel::email($user->emailNotification('inquiryAnswer', $data));
                        }
                    }
                }
                return true;
            }
        } else {
            return false;
        }
    }

    public function isMine()
    {
        $mine = isset($_SESSION['loggedIn']) && $_SESSION['loggedIn'] && $_SESSION['me'] == $this->user_id;
        return $mine;
    }

    public static function getAll($status = null, $page = null, $tag = null)
    {
        $questions = array();
        $sql = 'SELECT id FROM questions ';

        switch ($status) {
            case 'open':
                $sql .= 'WHERE closed = 0 ';
                break;
            case 'closed':
                $sql .= 'WHERE closed = 1 ';
                break;
            case 'all':
                $sql .= 'WHERE 1=1 ';
                break;
            case 'mine':
                $sql .= 'WHERE user_id = "'.$_SESSION['me'].'"';
                break;
        }

        $tag ? $sql .= 'AND tags LIKE "%'.$tag.'%" ' : null;

        $sql .= 'ORDER BY created DESC';

        $questionScope = BaseModel::fetch(array('id'), 'questions', array('closed' => 0));
        $questionScope = count($questionScope);

        if (isset($page) && intval($page) && $page > 0) {
            $maxPage = Application::getConfig()['pageMax'];
            $offset = $page * $maxPage - $maxPage;
            $maxRows = $maxPage;
            $sql .= ' LIMIT '.$offset.','.$questionScope;
        }

        $baseModel = new BaseModel();
        $query = $baseModel->db->prepare($sql);
        $query->execute();
        $rows = $query->fetchAll();

        if (count($rows) != 0) {

            foreach ($rows as $row) {
                $questions[] = new Question($row['id']);
            }
        }

        return $questions;
    }

        public static function getAllBySearch($status = null, $page = null, $keyword = null, $category = null)
    {
        $keyword = explode("!_!", $keyword);
        $numb = 0;

        $questions = array();
        $sql = 'SELECT id FROM questions ';

        $sql .= 'WHERE closed = 0 ';

        $category ? $sql .= 'AND category = "'.$category.'" ' : null;

        foreach ($keyword as $key) {

            $keyword ? $sql .= 'AND text LIKE "%'.$keyword[$numb].'%" ' : null;
            $keyword ? $sql .= 'OR descr LIKE "%'.$keyword[$numb].'%" ' : null;

            $numb++;
        }

        $keyword ? $sql .= 'AND text LIKE "%'.$keyword.'%" ' : null;

        $keyword ? $sql .= 'OR descr LIKE "%'.$keyword.'%" ' : null;

        $sql .= 'ORDER BY created DESC';

        $questionScope = BaseModel::fetch(array('id'), 'questions', array('closed' => 0));
        $questionScope = count($questionScope);

        if (isset($page) && intval($page) && $page > 0) {
            $maxPage = Application::getConfig()['pageMax'];
            $offset = $page * $maxPage - $maxPage;
            $maxRows = $maxPage;
            $sql .= ' LIMIT '.$offset.','.$questionScope;
        }
        
        $baseModel = new BaseModel();
        $query = $baseModel->db->prepare($sql);
        $query->execute();
        $rows = $query->fetchAll();

        if (count($rows) != 0) {

            foreach ($rows as $row) {
                $questions[] = new Question($row['id']);
            }
        }

        return $questions;
    }

    public static function addQuestion(array $params)
    {
        $error = false;
        $params['reward'] = btc($params['reward']);
        $params['reward'] >= Application::getConfig()['minReward'] ? null : $error = true;
        $user = new User($params['user_id']);

        if ($user) {
            $wallet = $user->getWallet();
            $params['reward'] <= $wallet['balance'] ? null : $error = true;
            $params['created'] = now();

            if (isset($params['file'])) {
                $params['file'] = BaseModel::upload($params['file'], 'uploads/questions/');

                if ($params['file'] != false) {
                    $filename = explode('.', $params['file'])[0];
                    $image = \PHPImageWorkshop\ImageWorkshop::initFromPath('uploads/questions/'.$params['file']);
                    $image->getWidth() > 1024 ? $image->resizeInPixel(1024, null, true) : null;
                    $image->save('uploads/questions/', $filename.'.jpg', false, '#ffffff', 75);
                } else {
                    $error = true;
                }
            }

            $error == false ? $query = BaseModel::insert('questions', $params) : $query = false;

            $question = BaseModel::fetch(array('id', 'reward'), 'questions', array('text' => $params['text']));

            $data['text'] = $params['text'];

            foreach ($question as $quest) {
                $question_id = $quest['id'];
                $hashed_question_id = md5($question_id);
                $data['hashed_id'] = $hashed_question_id;
                $data['reward'] = $quest['reward'];
            }

            if ($query) {
                if($params['question_lang'] == 'en') {
                    $notification = BaseModel::fetch(array('id', 'admin'), 'users');
                    foreach ($notification as $user) {
                        if ($user['admin'] == 1) {
                            $user = new User($user['id']);
                            BaseModel::email($user->emailNotification('newInquiryApproval', $data));
                        }
                    }
                }
                return true;
            }
        }
        
        return false;
    }

    public static function sendInquiryEmail($question_id)
    {
        $question = BaseModel::fetch(array('text', 'reward'), 'questions', array('id' => $question_id));

            foreach ($question as $quest) {
                $hashed_question_id = md5($question_id);
                $data['hashed_id'] = $hashed_question_id;
                $data['reward'] = $quest['reward'];
                $data['text'] = $quest['text'];
            }

        BaseModel::replaceVar('questions', array('email_sent' => '1'), array('id' => $question_id));

        $notification = BaseModel::fetch(array('id'), 'users', array('inquiry_email' => 1));
        foreach ($notification as $user) {
            $user = new User($user['id']);
            BaseModel::email($user->emailNotification('newInquiry', $data));
        }
    }

    public function getLang()
    {
        $question = new Question($this->id);
        $lang = $this->question_lang;
        return $lang;
    }

    public function getCategory()
    {
        $question = new Question($this->id);
        $category = $this->category;
        switch ($category) {
            case 'fash':
                $category = output('FASHION');
                break;

            case 'web':
                $category = output('TECH/WEB');
                break;

            case 'instr':
                $category = output('INSTRUCTION');
                break;

            default:
                $category = null;
                break;
        }
        return $category;
    }

    public static function changeQuestionLanguage($question_id, $new_language)
    {
        BaseModel::replaceVar('questions', array('question_lang' => $new_language), array('id' => $question_id));
    }

    public static function changeQuestionCategory($question_id, $new_category)
    {
        BaseModel::replaceVar('questions', array('category' => $new_category), array('id' => $question_id));
    }

    public static function editQuestion(array $params)
    {
        BaseModel::replaceVar('questions', array('text' => $params['question_text'], 'descr' => $params['question_descr']), array('id' => $params['question_id']));
    }
}