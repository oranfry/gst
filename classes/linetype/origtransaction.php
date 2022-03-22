<?php

namespace gst\linetype;

class origtransaction extends \jars\Linetype
{
    public function __construct()
    {
        $this->table = 'transaction';

        $this->fields = [
            'date' => fn ($records) : string => $records['/']->date,
        ];

        $this->unfuse_fields = [
            'date' => fn ($line, $oldline) : string => $line->date,
        ];
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
