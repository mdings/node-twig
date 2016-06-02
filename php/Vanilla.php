<?php
    // convert the arrays to objects
    function convertToObject ($array) {
        $object = new stdClass();
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $value = convertToObject($value);
            }
            $object->$key = $value;
        }
        return $object;
    }

    // render the php
    function render ($entry, $options) {
        $context =  $options['context'];
        $extensions = $options['extensions'];

        // include the extensions
        foreach ($extensions as $extension) {
          include_once $extension['file'];
        }

        // accept external json data as variables so they
        // can be accessed as object. e.g. $button->title;
        foreach ($context as $key => $value) {
            $$key = convertToObject($value);
        }

        // include the original template which now has access to the vars
        include_once "{$options['root']}/{$entry}";
    }
