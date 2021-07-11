<?php
namespace gst\linetype;

class gstsettlementgroup extends \Linetype
{
    public function __construct()
    {
        $this->table = 'transaction';
        $this->label = 'GST Settlment';
        $this->icon = 'moneytake';
        $this->id_field = 'date';
        $this->showass = ['list', 'calendar', 'graph'];
        $this->fields = [
            'icon' => function ($records) {
                return "moneytake";
            },
            'date' => function ($records) {
                return $records['/']->date;
            },
            'txdate' => function ($records) {
                if (!array_key_exists('/gstird_transaction', $records)) {
                    echo "gstsettlementgroup: no gstird_transaction record (?!)\n";
                    var_dump($records);
                    die();
                }

                return $records['/gstird_transaction']->date;
            },
            'account' => function ($records) {
                return "gst settlement";
            },
            'amount' => function ($records) {
                return $records['/']->amount;
            },
        ];
        $this->unfuse_fields = [
            'date' => function($line, $oldline) {
                return $line->date;
            },
        ];
        $this->inlinelinks = [
            (object) [
                'linetype' => 'origtransaction',
                'tablelink' => 'gstird',
                'reverse' => true,
                'required' => true,
            ],
        ];
    }

    public function has($line, $property)
    {
        return $property == 'gstird_transaction';
    }

    public function unpack($line)
    {
        $line->gstird_transaction = (object) [
            'date' => $line->txdate,
        ];
    }
}
