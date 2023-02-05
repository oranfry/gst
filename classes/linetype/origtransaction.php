<?php

namespace gst\linetype;

class origtransaction extends \jars\Linetype
{
    public function __construct()
    {
        $this->table = 'transaction';

        $this->simple_string('date');
    }

    public function validate($line)
    {
        $errors = [];

        if (@$line->date == null) {
            $errors[] = 'no date';
        }

        return $errors;
    }
}
