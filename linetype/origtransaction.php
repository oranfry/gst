<?php

namespace gst\linetype;

use simplefields\traits\SimpleFields;

class origtransaction extends \jars\Linetype
{
    use SimpleFields;

    public function __construct()
    {
        $this->table = 'transaction';

        $this->simple_date('date');
    }

    public function validate($line): array
    {
        $errors = parent::validate($line);

        if (@$line->date == null) {
            $errors[] = 'no date';
        }

        return $errors;
    }
}
