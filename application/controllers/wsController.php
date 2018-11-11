<?php

class wsController extends Controller
{
    
    public function __construct()
    {
        $this->model('User');
        $this->model('Question');
        $this->model('Ws');
    }
        public function allques($page = 1, $status = 'open', $tag = null)
    {
        $this->model('Question');
        $page < 1 ? $page = 1 : null;
        $data['page'] = $page;
        $data['status'] = $status;
        $data['tag'] = $tag;
        $data['questions'] = Question::getAll($data['status'], $data['page'], $tag);
        header("content-type: application/json");
        echo json_encode($data);
    }
    public function searchques($page = 1, $keyword, $category = null, $lang = null)
    {
        $this->model('Question');
        $page < 1 ? $page = 1 : null;
        $data['page'] = $page;
        $data['status'] = 'open';
        if ($keyword == "0") {
            $data['keyword'] = false;
        } else {
            $data['keyword'] = $keyword;
        }
        if ($category == "0") {
            $data['category'] = false;
        } else {
            $data['category'] = $category;
        }
        $data['lang'] = $lang;
        $data['questions'] = Question::getAllBySearch($data['status'], $data['page'], $data['keyword'], $data['category']);
        header("content-type: application/json");
        echo json_encode($data);
    }
    
    
    public function test()
    {
        if (isset($this->user)) {
            $this->user->setNodeBalance();
             $bitcoind = $this->getBitcoind();
             echo $this->loginws; echo "ffff";
            echo $this->user->getWallet()['balance'];
        } else {
            echo '0';
        }
    }    
    public function walletBalance()
    {
        if (isset($this->user)) {
            $this->user->setNodeBalance();
            echo $this->user->getWallet()['balance'];
        } else {
            echo '0';
        }
    }
    public function newq()
    {
        
        if (isset($this->request['questionSubmit'])) {

            $params = array(
                 'text' => $this->request['questionText'],
                'descr' => $this->request['questionDescr'],
                'tags' => $this->request['questionTags'],
                'reward' => $this->request['questionReward'],
                'user_id' => $this->request['questionUserid'],
                'question_lang' => $this->request['questionLanguage'],
                'category' => $this->request['questionCategory'],
            );

            is_uploaded_file($this->file['questionFile']['tmp_name']) ? $params['file'] = $this->file['questionFile'] : null;
            $data['question'] = Question::addQuestion($params);

            $data['question'] ?
                header('Location: '.Application::getConfig()['url'].'/question/show/'.md5($this->user->getQuestions()[0]->id)) :
                $data['alert'] = array('type' => 'error', 'text' => output('SOMETHING_WENT_WRONG'));
        }

        $data['questions'] = Question::getAll('open');
        echo json_encode($data);
    }
    
    
    
    
}
    ?>