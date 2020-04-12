<?php
namespace blend;

class salegst extends \Blend
{
    public function __construct()
    {
        $this->label = 'GST: Sales';
        $this->linetypes = ['salegst',];
        $this->past = false;
        $this->cum = true;
        $this->groupby = 'date';
        $this->fields = [
            (object) [
                'name' => 'icon',
                'type' => 'icon',
            ],
            (object) [
                'name' => 'date',
                'type' => 'date',
                'main' => true,
            ],
            (object) [
                'name' => 'account',
                'type' => 'account',
            ],
            (object) [
                'name' => 'gross',
                'type' => 'number',
                'dp' => 2,
                'summary' => 'sum',
            ],
            (object) [
                'name' => 'amount',
                'type' => 'number',
                'dp' => 2,
                'summary' => 'sum',
            ],
        ];
    }
}
