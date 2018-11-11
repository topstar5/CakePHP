<?php

class questionController extends Controller
{
    public function __construct()
    {
        $this->model('User');
        $this->model('Question');
    }

    public function submit()
    {
        if (isset($this->request['questionSubmit'])) {

            $params = array(
                'text' => $this->request['questionText'],
                'descr' => $this->request['questionDescr'],
                'tags' => $this->request['questionTags'],
                'reward' => $this->request['questionReward'],
                'user_id' => $this->user->id,
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
        $this->view('home/index', $data);
    }

    public function answer($questionId_md5)
    {
        $question = Question::getByMd5($questionId_md5);

        if ($question) {
            $success = $question->answer($this->user, nl2br($this->request['answerText']));
            $success ?
                header('Location: '.Application::getConfig()['url'].'/question/show/'.$questionId_md5.'/answerSuccess') :
                $data['alert'] = array('type' => 'error', 'text' => output('SOMETHING_WENT_WRONG'));
        }

        $data['questions'] = Question::getAll('open');
        !empty($this->user) ? $data['wallet'] = $this->user->getWallet() : $data['wallet'] = null;
        $this->view('home/index', $data);
    }

    public function commentAnswer($answerId_md5)
    {
        $answer = Answer::getByMd5($answerId_md5);

        if ($answer) {
            $success = $answer->comment($this->user, $this->request['commentText']);
            $success ?
                header('Location: '.Application::getConfig()['url'].'/question/show/'.md5($answer->question_id).'/commentSuccess') :
                $data['alert'] = array('type' => 'error', 'text' => output('SOMETHING_WENT_WRONG'));
        }

        $data['questions'] = Question::getAll('open');
        !empty($this->user) ? $data['wallet'] = $this->user->getWallet() : $data['wallet'] = null;
        $this->view('home/index', $data);
    }

    public function markAnswer($questionId_md5, $answerId_md5)
    {
        $success = false;
        $question = Question::getByMd5($questionId_md5);

        if ($question && $question->isMine()) {
            $answer = Answer::getByMd5($answerId_md5);

            if ($answer) {
                $success = $answer->markAsCorrect();
            }
        }

        $success ?
            header('Location: '.internalLink('question/show/'.md5($question->id).'/markAnswerSuccess')) :
            $data['alert'] = array('type' => 'error', 'text' => output('SOMETHING_WENT_WRONG'));
        $data['question'] = Question::getByMd5($questionId_md5);
        $this->view('question/show', $data);

    }

    public function show($questionId_md5, $param = null)
    {
        $data['question'] = Question::getByMd5($questionId_md5);

        if (isset($param)) {

            switch ($param) {
                case 'answerSuccess':
                    $data['alert'] = array('type' => 'success', 'text' => output('ANSWER_SUBMITTED'));
                    break;
                case 'markAnswerSuccess':
                    $data['alert'] = array('type' => 'success', 'text' => output('ANSWER_MARKED'));
                    break;
                case 'commentSuccess':
                    $data['alert'] = array('type' => 'success', 'text' => output('COMMENT_SUBMITTED'));
                    break;
            }
        }


        !$data['question'] ? $data['alert'] = array('type' => 'error', 'text' => 'Requested question is deleted.') : null;
        $this->view('question/show', $data);
    }

    public function sendEmail()
    {
        if (isset($this->request['id'])) {
            $question_id = $this->request['id'];
            Question::sendInquiryEmail($question_id);
        } else {
            $data['alert'] = array('type' => 'error', 'text' => 'No question_id is inputed.');
            $this->view('question/show', $data);
        }
    }

    public function changeLanguage()
    {
        if (isset($this->request['id'])) {
            $question_id = $this->request['id'];
            $new_language = $this->request['questionLanguage'];
            Question::changeQuestionLanguage($question_id, $new_language);
            $questionId_md5 = md5($question_id);
            $data['question'] = Question::getByMd5($questionId_md5);
            $this->view('question/show', $data);
        } else {
            $data['alert'] = array('type' => 'error', 'text' => 'No question_id is inputed.');
            $this->view('question/show', $data);
        }
    }

    public function changeCategory()
    {
        if (isset($this->request['id'])) {
            $question_id = $this->request['id'];
            $new_category = $this->request['questionCategory'];
            Question::changeQuestionCategory($question_id, $new_category);
            $questionId_md5 = md5($question_id);
            $data['question'] = Question::getByMd5($questionId_md5);
            $this->view('question/show', $data);
        } else {
            $data['alert'] = array('type' => 'error', 'text' => 'No question_id is inputed.');
            $this->view('question/show', $data);
        }
    }

    public function edit()
    {
        if (isset($this->request['id'])) {
            $question_id = $this->request['id'];
            $questionId_md5 = md5($question_id);
            $data['question'] = Question::getByMd5($questionId_md5);
            $this->view('question/edit', $data);
        } else {
            $data['alert'] = array('type' => 'error', 'text' => 'No question_id is inputed.');
            $this->view('question/show', $data);
        }
    }

    public function editQuestion()
    {
        if (isset($this->request['id'])) {
            $data['question_id'] = $this->request['id'];
            $data['question_text'] = $this->request['text'];
            $data['question_descr'] = $this->request['descr'];
            $edit = Question::editQuestion($data);
            $questionId_md5 = md5($data['question_id']);
            $data['question'] = Question::getByMd5($questionId_md5);
            return $this->view('question/show', $data);
        } else {
            $data['alert'] = array('type' => 'error', 'text' => 'No question_id is inputed.');
            return $this->view('question/show', $data);
        }
    }

    public function search()
    {
        if (isset($this->request['lang'])) {
            $data['lang'] = $this->request['lang'];
            $data['category'] = $this->request['category'];
            $data['keyword'] = $this->request['keyword'];
            $data['keyword'] = preg_replace('/\s+/', '!_!', $data['keyword']);

            if($data['keyword'] == null && $data['category'] == null) {
                $data['alert'] = array('type' => 'error', 'text' => 'Please select category or insert keyword for search.');
                return $this->view('home', $data);
            } else {
                echo "Searching...";
                if($data['keyword'] != null && $data['category'] != null) {
                ?>
                    <meta http-equiv="refresh" content="0; url=<?=internalLink('home/search/1/'.$data['keyword'].'/'.$data['category'].'/'.$data['lang'])?>" />
                <?php
                } else if ($data['keyword'] != null) {
                    $data['category'] = 0;
                ?>
                    <meta http-equiv="refresh" content="0; url=<?=internalLink('home/search/1/'.$data['keyword'].'/'.$data['category'].'/'.$data['lang'])?>" />
                <?php
                } else if ($data['category'] != null) {
                    $data['keyword'] = 0;
                ?>
                    <meta http-equiv="refresh" content="0; url=<?=internalLink('home/search/1/'.$data['keyword'].'/'.$data['category'].'/'.$data['lang'])?>" />
                <?php
                }
            }

        } else {
            $data['alert'] = array('type' => 'error', 'text' => 'No language selected.');
            return $this->view('home', $data);
        }
    }
}