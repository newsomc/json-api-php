<?php

require_once('Inflector.php');

class Responder {

    static $links = array();

    function __construct($type, $encoder = 'json_encode') {

        if (!$type) {
            throw new Exception('Type must be set.');
        }

        $this->encoder = $encoder;
        $this->type    = $type;
        $this->root    = $this->pluralizedType();
    }

    /**
     * Applies the speicified encoder to an object.
     *
     * @param array $assoc A php associative array.
     *
     * @return mixed[]
     *
     */

    private function adapter($assoc) {
        $func = $this->encoder;
        return $func($assoc);
    }

    private function buildMeta($meta) {
        return $meta;
    }

    private function buildLinks(array $links) {
        $rv = array();
        $properties = array();

        foreach($links as $link) {

            $properties = $this->links[$link];
            $key = sprintf("%s.%s", $this->pluralizedType(), $link);
            $value = array(
                "type" => $properties['responder']->root
            );

            if(array_key_exists("href", $properties)) {
                $value['href'] = $properties['href'];
            }

            $rv[$key] = $value;
        }

        return $rv;
    }

    private function buildLinked(array $linked) {
        $rv = array();

        foreach($linked as $key => $instances) {

            $responder = $this->links[$key]['responder'];

            $rv[$key]  = $responder->buildResources($instances);
        }
        return $rv;
    }

    private function buildResources($instance, $links = NULL) {
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

    /**
     * Public interface to `get()`
     *
     * @todo investigate a better way of  
     * args/destructuring in PHP.
     *      
     * @param mixed[] $args Array 
     *
     * keys include:
     *  - instnaces (mandatory)
     *  - meta 
     *  - links
     *  - linked
     * 
     * @return $data [] 
     *
     */
    public function build(array $args) {
        $data = $this->get($args);
        return $data;
    }

    public function dumps(array $args) {
        $formatted_data = $this->respond($args);
        return $formatted_data;
    }

    public function respond(array $args) {
        $document = $this->get($args);
        return $this->adapter($document);
    }

    private function get($args) {

        if (!$args['instances']) {
            throw new Exception('Instances key must be set.');
        }

        $instances = $args['instances'];
        $meta      = $args['meta'];
        $links     = $args['links'];
        $linked    = $args['linked'];
        $root      = $this->root;

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
        return Inflector::pluralize($this->type);
    }

}