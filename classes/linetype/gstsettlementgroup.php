<?php

namespace gst\linetype;

class gstsettlementgroup extends \jars\Linetype
{
    public function __construct()
    {
        $this->table = 'irdgst';

        $this->simple_string('date');
        $this->literal('account', 'irdgst');

        $this->fields['amount'] = fn ($records) => bcadd('0', $records['/']->amount, 2);
        $this->borrow['txdate'] = fn ($line) => $line->gstird_transaction->date;

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
