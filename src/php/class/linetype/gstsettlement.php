<?php
namespace linetype;

class gstsettlement extends \Linetype
{
    public function __construct()
    {
        $this->table = 'transaction';
        $this->label = 'GST Settlment';
        $this->icon = 'moneytake';
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
                'name' => 'account',
                'type' => 'text',
                'fuse' => 't.account',
            ],
            (object) [
                'name' => 'amount',
                'type' => 'number',
                'dp' => 2,
                'summary' => 'sum',
                'fuse' => 't.amount',
            ],
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
}
