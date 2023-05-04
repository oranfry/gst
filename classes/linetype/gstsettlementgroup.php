<?php

namespace gst\linetype;

use simplefields\traits\SimpleFields;

class gstsettlementgroup extends \jars\Linetype
{
    use SimpleFields;

    public function __construct()
    {
        $this->table = 'irdgst';

        $this->simple_date('date');
        $this->simple_literal('account', 'irdgst');

        $this->fields['amount'] = fn ($records): float => (float) bcadd('0', $records['/']->amount, 2);
        $this->borrow['txdate'] = fn ($line): string => $line->gstird_transaction->date;

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
