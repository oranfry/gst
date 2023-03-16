<?php

namespace gst\linetype;

class plaintransaction extends \jars\Linetype
{
    public function __construct()
    {
        $this->table = 'transaction';

        $this->simple_strings('date', 'account', 'description');
        $this->simple_floats('amount');
    }

    public function validate($line)
    {
        $errors = [];

        if (@$line->date == null) {
            $errors[] = 'no date';
        }

        if (@$line->amount == null) {
            $errors[] = 'no amount';
        }

        return $errors;
    }
}
