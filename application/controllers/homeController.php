<?php

class homeController extends Controller
{
    public function index($page = 1, $status = 'open', $tag = null)
    {
        $this->model('Question');
        $page < 1 ? $page = 1 : null;
        $data['page'] = $page;
        $data['status'] = $status;
        $data['tag'] = $tag;
        $data['questions'] = Question::getAll($data['status'], $data['page'], $tag);
        $this->view('home/index', $data);
    }

    public function search($page = 1, $keyword, $category = null, $lang = null)
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
        $this->view('home/index', $data);
    }
}
