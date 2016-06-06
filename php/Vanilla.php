<?php
    // convert the arrays to objects
    function convertToObject ($array) {
        $object = new stdClass();
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $value = convertToObject($value);
            }
            // replace function parentheses with Fn
            if(strpos($key, '()') > -1) {
                $key = str_replace('()', 'Fn', $key);
            }
            $object->$key = $value;
        }
        return $object;
    }

    function array_keys_multi($array) {
        $keys = array();
        foreach ($array as $key => $value) {
            $keys[] = $key;
            if (is_array($value)) {
                $keys = array_merge($keys, array_keys_multi($value));
            }
        }
        return $keys;
    }

    function hasParentheses($var) {
        if(preg_match('#\((.*?)\)#', $var, $out)) {
            return $var;
        }
    }

    // render the php
    function render ($entry, $options) {
        $context =  $options['context'];
        $extensions = $options['extensions'];
        $templateName = "{$options['root']}/.tmp/template.inc.php";

        // include the extensions
        foreach ($extensions as $extension) {
          include_once $extension['file'];
        }

        // accept external json data as variables so they
        // can be accessed as object. e.g. $button->title;
        foreach ($context as $key => $value) {
            $$key = convertToObject($value);
        }

        // find mocked function calls (contain parentheses) in the JSON structure
        $keys = array_filter(array_keys_multi($context), "hasParentheses");

        // read the content
        $content = file_get_contents("{$options['root']}/{$entry}");

        // check if function calls from array filter exist and replace them with Fn
        // e.g. $tree->build() ==> $tree->buildFn;
        foreach ($keys as $key) {
            $replace = preg_replace('/\(.*?\)/', 'Fn', $key);
            $content = str_replace($key, $replace, $content);
        }

        // create the directory if it doesn't exist yet
        if (!file_exists("{$options['root']}/.tmp/")) {
            mkdir("{$options['root']}/.tmp/", 0777, true);
        }

        // delete the previously generated template name
        if (file_exists($templateName)) {
            unlink($templateName);
        }

        // create the new one
        file_put_contents($templateName, $content, FILE_APPEND);

        // include the file to be outputted
        include_once($templateName);
    }
