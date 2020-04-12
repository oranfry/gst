<?php
namespace linetype;

class purchasegst extends gst
{
    public function __construct()
    {
        parent::__construct();

        $this->clause = "t.description = 'purchase' or ifnull(t.description, '') != 'sale' and t.amount < 0";
        filter_objects($this->fields, 'name', 'is', 'amount')[0]->fuse = '-t.amount';
        filter_objects($this->fields, 'name', 'is', 'gross')[0]->fuse = '-ifnull(gstpeer_transaction.amount, 0) - t.amount';
    }
}
