<?php
namespace tablelink;

class gstpeer extends \Tablelink
{
    public function __construct()
    {
        $this->tables = ['transaction', 'transaction'];
        $this->middle_table = 'tablelink_gst_peer';
        $this->ids = ['gstpeer_transaction', 'gstpeer_gst'];
        $this->type = 'oneone';
    }
}
