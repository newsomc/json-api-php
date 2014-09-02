JSON API PHP
===
JSON-API responses in PHP.


About
-----
JSON API PHP is a miro library implementing _must_ parts of the
[JSON-API](http://jsonapi.org) response specification. This means that
you can use JSON API PHP to serialize your models into responses that
contain links and linked compound documents. This is essentially a
port of Hyp (Python) at the moment. 


Example
------
```php

    $post = array(
        'id'=> 1,
        'title'=> 'My post',
        'comments' => array(
            array('id'=> 1, 'content'=> 'A comment'),
            array('id'=> 2, 'content' =>'Another comment')));
    
    $pr = new PostResponder;
    $json = $pr->dumps(array(
        'instances' => $post, 
        'linked' => array('comments'=> $post['comments'])
    ));
```
