<?php
namespace gst\blend;

class gst extends \Blend
{
    public function __construct()
    {
        $this->label = 'All';
        $this->linetypes = ['transaction',];
        $this->past = false;
        $this->cum = true;
        $this->groupby = 'date';

        $this->filters = [
            (object) [
                'field' => 'gst',
                'cmp' => '!=',
                'value' => 0,
            ],
        ];

        $this->fields = [
            (object) [
                'name' => 'date',
                'type' => 'date',
                'main' => true,
            ],
            (object) [
                'name' => 'sort',
                'type' => 'text',
            ],
            (object) [
                'name' => 'account',
                'type' => 'text',
            ],
            (object) [
                'name' => 'gst',
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
