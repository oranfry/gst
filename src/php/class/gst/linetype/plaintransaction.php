<?php
namespace gst\linetype;

class plaintransaction extends \Linetype
{
    public function __construct()
    {
        $this->label = 'Transaction';
        $this->icon = 'dollar';
        $this->table = 'transaction';
        $this->fields = null;
        $this->summaries = null;
        $this->showass = ['list', 'calendar', 'graph'];
        $this->fields = [
            'icon' => function ($records) : string {
                return 'dollar';
            },
            'date' => function ($records) : string {
                return $records['/']->date;
            },
            'account' => function ($records) : ?string {
                return @$records['/']->account;
            },
            'description' => function ($records) : ?string {
                return @$records['/']->description;
            },
            'amount' => function ($records) : ?string {
                return $records['/']->amount;
            },
        ];
        $this->unfuse_fields = [
            'date' => function($line, $oldline) : string {
                return $line->date;
            },
            'amount' => function($line, $oldline) : string {
                return $line->amount;
            },
            'account' => function($line, $oldline) : string {
                return $line->account;
            },
            'description' => function($line, $oldline) : ?string {
                return $line->description;
            },
        ];
    }

    public function get_suggested_values($token)
    {
        $suggested_values = [];

        $suggested_values['account'] = get_values($token, 'transaction', 'account');

        return $suggested_values;
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
