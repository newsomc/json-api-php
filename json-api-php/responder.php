<?php
require_once('Inflector.php');

class Responder {

    static $encoder;
    static $root;
    
    // why the all caps?
    static $LINKS = array();
    static $TYPE;

    function __construct($type, $encoder = 'json_encode') {

        if (!$type) {
            throw new Exception('Type must be set.');
        }

        $this->encoder = $encoder;
        $this->TYPE = $type;
        $this->root = $this->pluralizedType();  // i would sent $this->type in as the argument
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
        // huh?  this seems weird, do you want to 
        // foreach ($instances as $instance) { }  // but not sure what your return is
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
        // seems like mixing behavior.  build calls get?  seems like build performs and action but get should return data
        return $this->get($args);
    }

    public function dumps(array $args) {
        return $this->respond($args);
    }

    public function respond(array $args) {
        $document = $this->get($args);
        return $this->adapter($document);
    }

    // get seems to do a lot more than just getting data, i would maybe separate out this action into set and get
    public function get($args) {
        // this seems like trouble if thise arg keys are not set.  
        $instances = $args['instances'];
        $meta      = $args['meta'];
        $links     = $args['links'];
        $linked    = $args['linked'];
        $root = $this->root;

        if (!is_array($instances)) {
            $instances = array($instances);
        }

        if ($linked != NULL) {
            // $links vs $linked is confusing
            $links = array_keys($linked);
        }

        $document = array();
        
        // this section is confusing since you deal with $linked/$links above, maybe tigthen this logic
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
        return Inflector::pluralize($this->TYPE);  // make this a function argument?
    }

}
