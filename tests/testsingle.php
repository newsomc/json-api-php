<?php

require_once('fixtures.php');

function test_single_object() {
    $test_data = array('id' => 1, 'title'=> 'My title');
    $pr = new PostResponder;
    $data = $pr->dumps(array('instances' => $test_data));
    assert($data == json_encode(array('posts' => $test_data))); 
}

function test_multiple_objects() {
    $test_data = array(
        array('id' => 1, 'title' => 'A title'),
        array('id' => 2, 'title' => 'Another title')
    );
    $pr = new PostResponder;
    $data = $pr->dumps(array('instances' => $test_data));

    assert($data == json_encode(array('posts' => array(
        array('id'=> 1, 'title'=> 'A title'),
        array('id'=> 2, 'title'=> 'Another title')
    ))));
}

function test_meta() {
    $test_data = array(
        'instances' => array('id'=> 1, 'title'=> 'Yeah'),
        'meta'=> array('key'=> 'value')
    );
    $pr = new PostResponder;
    $data = $pr->build($test_data);

    assert($data['meta']['key'] == 'value');
}

function test_dumps() {
    $test_data = array('instances' => array('id'=> 1, 'title'=> 'A title'));
    $pr = new PostResponder;
    $data = $pr->build($test_data);

    assert(json_encode($data) == json_encode(
        array("posts" => array("id" => 1, "title" => "A title"))));
}

function dump_object() {
    $post = array(
        'id'=> 1,
        'title'=> 'My post',
        'comments' => array(
            array('id'=> 1, 'content'=> 'A comment'),
            array('id'=> 2, 'content' =>'Another comment')));
    
    $pr = new PostResponder;

    $json = $pr->dumps(array(
        'instances' => $post,
        'linked' => array(
            'comments' => $post['comments']
        )));

    var_dump($json);
}


// Run tests
function run() {
    test_single_object();
    test_multiple_objects();
    test_meta();
    test_dumps();
    dump_object();
}

run();


