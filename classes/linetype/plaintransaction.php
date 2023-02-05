<?php

namespace gst\linetype;

class plaintransaction extends \jars\Linetype
{
    public function __construct()
    {
        $this->table = 'transaction';

        $this->fields = [
            'date' => fn ($records) : string => $records['/']->date,
            'account' => fn ($records) : ?string => @$records['/']->account,
            'description' => fn ($records) : ?string => @$records['/']->description,
            'amount' => fn ($records) : ?string => $records['/']->amount,
        ];

        $this->unfuse_fields = [
            'date' => fn ($line, $oldline) : string => $line->date,
            'amount' => fn ($line, $oldline) : string => $line->amount,
            'account' => fn ($line, $oldline) : string => $line->account,
            'description' => fn ($line, $oldline) : ?string => $line->description,
        ];
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
