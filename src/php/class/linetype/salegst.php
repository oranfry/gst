<?php
namespace linetype;

class salegst extends gst
{
    public function __construct()
    {
        parent::__construct();

        $this->clause = "t.description = 'sale' or ifnull(t.description, '') != 'purchase' and t.amount > 0";
    }
}