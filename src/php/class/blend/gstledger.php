<?php
namespace blend;

class gstledger extends \Blend
{
    function __construct()
    {
        $this->label = 'GST: Ledger';
        $this->past = true;
        $this->cum = true;
        $this->linetypes = ['gsttransaction', 'gstsettlementgroup', 'error', 'correction',];
        $this->hide_types = ['gstsettlementgroup' => 'gstsettlementgroup'];
        $this->showass = ['list', 'calendar', 'graph', 'summaries'];
        $this->groupby = 'date';
        $this->fields = [
            (object) [
                'name' => 'icon',
                'type' => 'icon',
                'derived' => true,
            ],
            (object) [
                'name' => 'date',
                'type' => 'date',
                'groupable' => true,
                'main' => true,
            ],
            (object) [
                'name' => 'account',
                'type' => 'text',
            ],
            (object) [
                'name' => 'description',
                'type' => 'text',
                'default' => '',
                'sacrifice' => true,
            ],
            (object) [
                'name' => 'parenttype',
                'type' => 'icon',
                'derived' => true,
                'translate' => ['buy' => 'basket', 'sale' => 'piggybank'],
                'customlink' => '"/line?type={$parenttype}&id={$parentid}&back=' . base64_encode($_SERVER['REQUEST_URI']) . '"',
            ],
            (object) [
                'name' => 'hasgst',
                'type' => 'icon',
                'default' => '',
            ],
            (object) [
                'name' => 'amount',
                'type' => 'number',
                'summary' => 'sum',
                'dp' => 2,
            ],
            (object) [
                'name' => 'broken',
                'type' => 'class',
                'default' => '',
            ],
        ];
    }
}
