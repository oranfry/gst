<?php
namespace linetype;

class gstsettlementgroup extends \Linetype
{
    public function __construct()
    {
        $this->table = 'transaction';
        $this->label = 'GST Settlment';
        $this->icon = 'moneytake';
        $this->id_field = 'date';
        $this->showass = ['list', 'calendar', 'graph'];
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
                'fuse' => 't.date',
            ],
            (object) [
                'name' => 'txdate',
                'type' => 'date',
                'fuse' => 'gstird_transaction.date',
            ],
            (object) [
                'name' => 'account',
                'type' => 'text',
                'fuse' => "'gst settlement'",
            ],
            (object) [
                'name' => 'amount',
                'type' => 'number',
                'dp' => 2,
                'summary' => 'sum',
                'fuse' => 't.amount',
            ],
        ];
        $this->unfuse_fields = [
            't.date' => ':date',
        ];
        $this->inlinelinks = [
            (object) [
                'linetype' => 'gstfreetransaction',
                'tablelink' => 'gstird',
                'reverse' => true,
                'required' => true,
            ],
        ];
    }

    public function has($line, $assoc)
    {
        return $assoc == 'gstird_transaction';
    }
}
