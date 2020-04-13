<?php
namespace blend;

class gst extends \Blend
{
    public function __construct()
    {
        $this->label = 'Net';
        $this->linetypes = ['gst',];
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
