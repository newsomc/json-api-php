<?php
require_once('Inflector.php');

class Responder {

    static $encoder;
    static $root;
    static $LINKS = array();
    static $TYPE;

    function __construct($type, $encoder = NULL) {

        if (!$type){
            throw new Exception('Type must be set.');
        }

        $this->encoder = isset($encoder) ? $encoder : 'json_encode';
        $this->TYPE = $type;
        $this->root = $this->pluralizedType();
    }

    private function adapter($obj) {
        $func = $this->encoder;
        return $func($obj);
    }

    private function buildMeta($meta) {
        return $meta;
    }

    private function buildLinks($links) {
        $rv = array();
        $properties = array();

        foreach($links as $link) {
            $properties = $this->LINKS[$link];
            $key = sprintf("%s.%s", $this->pluralizedType(), $link);
            $value = array(
                "type" => $properties['responder']->pluralizedType()
            );

            if(array_key_exists("href", $properties)) {
                $value['href'] = $properties['href'];
            }

            $rv[$key] = $value;
        }

        return $rv;
    }

    private function buildLinked($linked) {
        $rv = array();

        foreach($linked as $key => $instances) {

            $responder = $this->LINKS[$key]['responder'];

            $rv[$key]  = $responder->buildResources($instances);
        }
        return $rv;
    }

    private function buildResources($instances, $links = NULL) {
        return $this->buildResource($instances, $links);
    }

    private function buildResource($instance, $links = NULL) {
        $resource = $instance;
        if($links != NULL) {
            $resource['links'] = $this->buildResourceLinks($instance, $links);
        }
        return $resource;
    }

    private function buildResourceLinks($instance, $links) {
        $resourceLinks = array();

        foreach($links as $link) { 
            $related = $instance[$link];
            if (is_array($related)) {
                foreach($related as $r) {
                    $resourceLinks[$link][] = $r['id']; 
                }
            } else if ($related != NULL) {
                $resourceLinks[$link] = $related;
            }

        }
        return $resourceLinks;
    }

    public function build(array $args) {
        return $this->get($args);
    }

    public function dumps(array $args) {
        return $this->respond($args);
    }

    public function respond(array $args) {
        $document = $this->get($args);
        return $this->adapter($document);
    }

    public function get(&$args) {
        $instances = $args['instances'];
        $meta      = $args['meta'];
        $links     = $args['links'];
        $linked    = $args['linked'];
        $root = $this->root;

        if (!is_array($instances)) {
            $instances = array($instances);
        }

        if ($linked != NULL) {
            $links = array_keys($linked);
        }

        $document = array();

        if ($meta != NULL) {
            $document['meta'] = $this->buildMeta($meta);
        }

        if ($links != NULL) {
            $document['links'] = $this->buildLinks($links);
        }

        if ($linked != NULL) {
            $document['linked'] = $this->buildLinked($linked);
        }

        $document[$root] = $this->buildResources($instances, $links);

        return $document; 
    }

    public function pluralizedType() {
        return Inflector::pluralize($this->TYPE);
    }

}