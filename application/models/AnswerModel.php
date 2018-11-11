<?php

class Answer extends BaseModel
{
    public $id;
    public $text;
    public $user_id;
    public $created;
    public $correct;
    public $question_id;
    public $answer_id;
    public $user;
    public $comments;

    public function __construct($id)
    {
        $query = BaseModel::fetch(array('*'), 'answers', array('id' => $id), array('LIMIT 1'));

        if (count($query) != 0) {
            $this->set_object_vars($this, $query[0]);
            $this->user = $this->getUser();
            $this->comments = $this->getComments();
            return $this;
        } else {
            return false;
        }
    }

    public static function getByMd5($answerId_md5)
    {
        $answer = BaseModel::getByMd5Id('answers', $answerId_md5);

        if ($answer) {
            $answer = new Answer($answer['id']);
        } else {
            $answer = false;
        }

        return $answer;
    }

    public function getUser()
    {
        $user = new User($this->user_id);
        return $user;
    }

    public function allowedToComment($userObj)
    {
        $question = new Question($this->question_id);

        if (!$question->closed) {
            if ($userObj->id == $question->user_id) {
                return true;
            } else if ($userObj->id == $this->user_id) {
                if (count($this->comments) != 0 && $this->comments[count($this->comments) - 1]->user->id != $userObj->id) {
                    return true;
                }
            }
        }

        return false;
    }

    public function getComments()
    {
        $comments = array();
        $query = BaseModel::fetch(array('id'), 'answers', array('answer_id' => $this->id, 'question_id' => $this->question_id));

        if ($query && count($query) != 0) {
            foreach ($query as $row) {
                $comments[] = new Answer($row['id']);
            }
        }

        return $comments;
    }

    public function comment($userObj, $text)
    {
        $question = new Question($this->question_id);

        if ($question) {
            $op = $question->getUser();

            if ($userObj->id == $this->user_id || $userObj->id == $op->id) {
                $vals = array(
                    'text' => $text,
                    'user_id' => $userObj->id,
                    'created' => now(),
                    'answer_id' => $this->id,
                    'question_id' => $question->id
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
            }
        }

        return false;
    }

    public function markAsCorrect()
    {
        $question = new Question($this->question_id);
        $query = BaseModel::fetch(array('*'), 'answers', array('question_id' => $this->question_id, 'correct' => '1'));

        if (count($query) == 0) {
            $sender = $question->user->walletAccount();
            $receiver = $this->user->walletAccount();
            $bitcoind = $this->getBitcoind();
            $receiver_id = $this->user->getUserId();

            if ($bitcoind) {
                $tx = $bitcoind->move($sender, $receiver, btc($question->reward), Application::getConfig()['minconf']);

                if ($tx) {
                    $question->user->setNodeBalance();
                    $this->user->setNodeBalance();
                    BaseModel::replaceVar('answers', array('correct' => '1'), array('id' => $this->id));
                    BaseModel::replaceVar('questions', array('closed' => '1'), array('id' => $this->question_id));
                    $this->correct = true;

                    $data['hashed_id'] = md5($this->question_id);

                    $question_notification = BaseModel::fetch(array('reward', 'text'), 'questions', array('id' => $this->question_id));
                    foreach ($question_notification as $quest) {
                        $data['reward'] = $quest['reward'];
                        $data['text'] = $quest['text'];
                    }

                    $notification = BaseModel::fetch(array('id', 'bitcoin_win_email'), 'users', array('id' => $receiver_id));
                        foreach ($notification as $user) {
                            if ($user['bitcoin_win_email'] == 1) {
                                $user = new User($user['id']);
                                BaseModel::email($user->emailNotification('bitcoinWin', $data));
                            }
                        }
                    return true;
                }
            }
        }

        return false;
    }
}