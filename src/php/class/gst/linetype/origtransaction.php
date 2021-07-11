<?php
namespace gst\linetype;

class origtransaction extends \Linetype
{
    public function __construct()
    {
        $this->label = 'Orig Transaction';
        $this->table = 'transaction';
        $this->showass = ['list', 'calendar', 'graph'];
        $this->fields = [
            'date' => function ($records) : string {
                return $records['/']->date;
            },
        ];
        $this->unfuse_fields = [
            'date' => function($line, $oldline) : string {
                return $line->date;
            },
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
