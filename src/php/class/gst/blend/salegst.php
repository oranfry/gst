<?php
namespace gst\blend;

class salegst extends gst
{
    public function __construct()
    {
        parent::__construct();

        $this->label = 'Sales';
        $this->filters[] = (object)[
            'field' => 'sort',
            'cmp' => '=',
            'value' => 'sale',
        ];

        filter_objects($this->fields, 'name', 'is', 'sort')[0]->hide = true;
    }
}
