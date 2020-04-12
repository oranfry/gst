<?php
namespace linetype;

class gsttransaction extends \Linetype
{
    public function __construct()
    {
        $this->table = 'transaction';
        $this->label = 'Transaction';
        $this->icon = 'dollar';
        $this->clause = 'gstpeer_transaction.id is null and gstird_transaction.id is null and errorerror.id is null and correctionerror.id is null';
        $this->showass = ['list', 'calendar', 'graph'];
        $this->fields = [
            (object) [
                'name' => 'icon',
                'type' => 'icon',
                'fuse' => "'dollar'",
                'derived' => true,
            ],
            (object) [
                'name' => 'date',
                'type' => 'date',
                'id' => true,
                'groupable' => true,
                'fuse' => 't.date',
            ],
            (object) [
                'name' => 'account',
                'type' => 'text',
                'suggest' => 'true',
                'fuse' => 't.account',
            ],
            (object) [
                'name' => 'description',
                'type' => 'text',
                'fuse' => 't.description',
            ],
            (object) [
                'name' => 'invert',
                'type' => 'text',
                'constrained' => true,
                'fuse' => "if (gstpeer_gst.amount > 0 and gstpeer_gst.description = 'purchase' or gstpeer_gst.amount < 0 and gstpeer_gst.description = 'sale', 'yes', null)",
            ],
            (object) [
                'name' => 'claimdate',
                'type' => 'text',
                'fuse' => 'gstird_gst.date',
            ],
            (object) [
                'name' => 'hasgst',
                'type' => 'icon',
                'derived' => true,
                'fuse' => "if (gstpeer_gst.amount != 0, 'moneytake', '')",
            ],
            (object) [
                'name' => 'broken',
                'type' => 'class',
                'derived' => true,
                'fuse' => "if (t.account = 'error' or t.account = 'correction' or t.account = 'gst' or gstpeer_gst.amount + gstird_gst.amount != 0 or (gstpeer_gst.amount != 0 and abs(round(t.amount * 0.15, 2) - gstpeer_gst.amount) > 0.01), 'broken', '')",
            ],
            (object) [
                'name' => 'net',
                'type' => 'number',
                'dp' => 2,
                'summary' => 'sum',
                'fuse' => 't.amount',
            ],
            (object) [
                'name' => 'gst',
                'type' => 'number',
                'dp' => 2,
                'summary' => 'sum',
                'fuse' => 'gstpeer_gst.amount',
            ],
            (object) [
                'name' => 'amount',
                'type' => 'number',
                'dp' => 2,
                'derived' => true,
                'show_derived' => true,
                'summary' => 'sum',
                'fuse' => 'coalesce(t.amount, 0) + coalesce(gstpeer_gst.amount, 0)',
            ],
        ];

        $this->unfuse_fields = [
            't.date' => ':date',
            't.account' => ':account',
            't.description' => ':description',
            't.amount' => ':net',
            'gstpeer_gst.date' => ':date',
            'gstpeer_gst.account' => "'gst'",
            'gstpeer_gst.amount' => ':gst',
            'gstpeer_gst.description' => "if (:invert = 'yes', if(:gst > 0, 'purchase', 'sale'), null)",
            'gstird_gst.date' => ':claimdate',
            'gstird_gst.account' => "'gst'",
            'gstird_gst.amount' => '0 - :gst',
            'gstird_gst.description' => "if (:invert = 'yes', if(:gst > 0, 'purchase', 'sale'), null)",
        ];

        $this->inlinelinks = [
            (object) [
                'linetype' => 'gstfreetransaction',
                'tablelink' => 'gstpeer',
            ],
            (object) [
                'linetype' => 'gstfreetransaction',
                'tablelink' => 'gstird',
            ],
            (object) [
                'linetype' => 'gstfreetransaction',
                'tablelink' => 'gstpeer',
                'norecurse' => true,
                'reverse' => true,
            ],
            (object) [
                'linetype' => 'gstfreetransaction',
                'tablelink' => 'gstird',
                'norecurse' => true,
                'reverse' => true,
            ],
            (object) [
                'linetype' => 'error',
                'tablelink' => 'errorerror',
                'norecurse' => true,
                'reverse' => true,
                'alias' => 'errorerror',
            ],
            (object) [
                'linetype' => 'error',
                'tablelink' => 'errorcorrection',
                'norecurse' => true,
                'reverse' => true,
                'alias' => 'correctionerror',
            ],
        ];
    }

    public function complete($line)
    {
        $gstperiod = \Period::load('gst');

        if (!@$line->claimdate) {
            $line->claimdate = date_shift($gstperiod->rawstart($line->date), "+{$gstperiod->step} +1 month -1 day");
        }

        if (!@$line->net && !@$line->gst && @$line->amount) {
            $sign = $line->amount < 0 ? '-' : '';
            $abs = preg_replace('/^-/', '', $line->amount);
            $line->net = $sign . bcmul('1', bcadd(bcdiv(bcmul($abs, '100', 3), '115', 3), '0.005', 3), 2);
            $line->gst = bcsub($line->amount, $line->net, 2);
        }
    }

    public function has($line, $assoc)
    {
        if (in_array($assoc, ['gstpeer_gst', 'gstird_gst'])) {
            return @$line->gst != 0;
        }
    }

    public function get_suggested_values()
    {
        $suggested_values = [];

        $suggested_values['account'] = get_values('transaction', 'account');
        $suggested_values['invert'] = ['', 'yes'];

        return $suggested_values;
    }

    public function validate($line)
    {
        $errors = [];

        if ($line->date == null) {
            $errors[] = 'no date';
        }

        if ($line->account == null) {
            $errors[] = 'no item';
        }

        if ($line->net == null && $line->amount == null) {
            $errors[] = 'no price';
        }

        return $errors;
    }
}
