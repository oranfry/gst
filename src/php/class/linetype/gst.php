<?php
namespace linetype;

class gst extends \Linetype
{
    public function __construct()
    {
        $this->table = 'transaction';
        $this->label = 'GST';
        $this->icon = 'moneytake';
        $this->inlinelinks = [
            (object)[
                'tablelink' => 'gstpeer',
                'linetype' => 'gstfreetransaction',
                'reverse' => true,
                'required' => true,
            ],
        ];
        $this->fields = [
            (object) [
                'name' => 'icon',
                'type' => 'icon',
                'fuse' => "'moneytake'",
                'derived' => true,
            ],
            (object) [
                'name' => 'date',
                'type' => 'date',
                'id' => true,
                'groupable' => true,
                'fuse' => 't.date',
            ],
            (object) [
                'name' => 'account',
                'type' => 'text',
                'suggest' => 'true',
                'fuse' => 'gstpeer_transaction.account',
            ],
            (object) [
                'name' => 'description',
                'type' => 'text',
                'fuse' => 't.description',
            ],
            (object) [
                'name' => 'gross',
                'type' => 'number',
                'dp' => 2,
                'summary' => 'sum',
                'fuse' => 'ifnull(gstpeer_transaction.amount, 0) + t.amount',
            ],
            (object) [
                'name' => 'amount',
                'type' => 'number',
                'dp' => 2,
                'summary' => 'sum',
                'fuse' => 't.amount',
            ],
        ];
    }
}
