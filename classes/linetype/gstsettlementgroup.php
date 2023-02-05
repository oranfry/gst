<?php

namespace gst\linetype;

class gstsettlementgroup extends \jars\Linetype
{
    public function __construct()
    {
        $this->table = 'transaction';

        $this->fields = [
            'date' => fn ($records) => $records['/']->date,
            'account' => fn ($records) => "gst settlement",
            'amount' => fn ($records) => $records['/']->amount,
        ];

        $this->borrow = [
            'txdate' => fn ($line) => $line->gstird_transaction->date,
        ];

        $this->unfuse_fields = [
            'date' => fn ($line, $oldline) => $line->date,
        ];

        $this->inlinelinks = [
            (object) [
                'linetype' => 'origtransaction',
                'property' => 'gstird_transaction',
                'tablelink' => 'gstird',
                'reverse' => true,
            ],
        ];
    }

    public function unpack($line, $oldline, $old_inlines)
    {
        $line->gstird_transaction = (object) [
            'date' => $line->txdate,
        ];
    }
}
