<?php

require_once('../json-api-php/responder.php');

class CommentResponder extends Responder {
    public function __construct() {
        $this->id = 19988;
        $this->content = 'Really cool post man!';
        parent::__construct('comment', 'json_encode');
    }
}

class PersonResponder extends Responder {
    public function __construct() {
        $this->id = 22;
        $this->name = 'Joe';
        parent::__construct('person');
    }
}

class PostResponder extends Responder {
    public function __construct() {
        $this->id = 1;
        $this->title = 'Test';
        $this->links = array(
            'comments' => array(
                'responder' => new CommentResponder,
                'href'      => 'http://example.com/comments/{posts.comments}'
            ),
            'author' => array(
                'responder' => new PersonResponder,
                'href'      => 'http://example.com/comments/{posts.author}'
            )
        );
        parent::__construct('post');
    }
}
