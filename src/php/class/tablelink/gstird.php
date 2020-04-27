<?php
namespace tablelink;

class gstird extends \Tablelink
{
    public function __construct()
    {
        $this->tables = ['transaction', 'transaction'];
        $this->middle_table = 'tablelink_gst_ird';
        $this->ids = ['gstird_transaction', 'gstird_gst'];
        $this->type = 'oneone';
    }
}
