<?php
namespace gst\linetype;

class gsttransaction extends \Linetype
{
    public function __construct()
    {
        $this->table = 'transaction';
        $this->label = 'Transaction';
        $this->icon = 'dollar';
        $this->showass = ['list', 'calendar', 'graph'];
        $this->clauses = [
            '{t}_gstpeer_transaction.id is null and {t}_gstird_transaction.id is null'
        ];
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
                'fuse' => '{t}.date',
            ],
            (object) [
                'name' => 'account',
                'type' => 'text',
                'suggest' => 'true',
                'fuse' => '{t}.account',
            ],
            (object) [
                'name' => 'description',
                'type' => 'text',
                'fuse' => '{t}.description',
            ],
            (object) [
                'name' => 'sort',
                'type' => 'text',
                'constrained' => true,
                'fuse' => "coalesce(if({t}_gstpeer_gst.description in ('sale', 'purchase'), {t}_gstpeer_gst.description, null), if({t}_gstpeer_gst.amount > 0, 'sale', if({t}_gstpeer_gst.amount < 0, 'purchase', '')))",
            ],
            (object) [
                'name' => 'invert',
                'type' => 'text',
                'fuse' => "if({t}_gstpeer_gst.amount > 0 and {t}_gstpeer_gst.description = 'purchase' or {t}_gstpeer_gst.amount < 0 and {t}_gstpeer_gst.description = 'sale', 'yes', '')",
            ],
            (object) [
                'name' => 'claimdate',
                'type' => 'date',
                'fuse' => '{t}_gstird_gst.date',
            ],
            (object) [
                'name' => 'hasgst',
                'type' => 'icon',
                'derived' => true,
                'fuse' => "if ({t}_gstpeer_gst.amount != 0, 'moneytake', '')",
            ],
            (object) [
                'name' => 'net',
                'type' => 'number',
                'dp' => 2,
                'summary' => 'sum',
                'fuse' => '{t}.amount',
            ],
            (object) [
                'name' => 'gst',
                'type' => 'number',
                'dp' => 2,
                'summary' => 'sum',
                'fuse' => '{t}_gstpeer_gst.amount',
            ],
            (object) [
                'name' => 'amount',
                'type' => 'number',
                'dp' => 2,
                'derived' => true,
                'show_derived' => true,
                'summary' => 'sum',
                'fuse' => 'coalesce({t}.amount, 0) + coalesce({t}_gstpeer_gst.amount, 0)',
            ],
            (object) [
                'name' => 'file',
                'type' => 'file',
                'icon' => 'docpdf',
                'path' => 'transaction',
            ],
            (object) [
                'name' => 'broken',
                'type' => 'class',
                'derived' => true,
                'fuse' => "case
                        when {t}.account in ('error', 'correction', 'gst') then 'Reserved Account'
                        when {t}_gstpeer_gst.amount + {t}_gstird_gst.amount != 0 then 'Unbalanced GST'
                        when {t}_gstpeer_gst.amount != 0 and abs(round({t}.amount * 0.15, 2) - {t}_gstpeer_gst.amount) > 0.01 then 'Wrong GST'
                    end",
            ],
        ];

        $this->unfuse_fields = [
            '{t}.date' => (object) [
                'expression' => ':{t}_date',
                'type' => 'date',
            ],
            '{t}.account' => (object) [
                'expression' => ':{t}_account',
                'type' => 'varchar(40)',
            ],
            '{t}.description' => (object) [
                'expression' => ':{t}_description',
                'type' => 'varchar(255)',
            ],
            '{t}.amount' => (object) [
                'expression' => ':{t}_net',
                'type' => 'decimal(18, 2)',
            ],
        ];

        $this->inlinelinks = [
            (object) [
                'linetype' => 'plaintransaction',
                'tablelink' => 'gstpeer',
            ],
            (object) [
                'linetype' => 'plaintransaction',
                'tablelink' => 'gstird',
            ],
            (object) [
                'linetype' => 'plaintransaction',
                'tablelink' => 'gstpeer',
                'norecurse' => true,
                'reverse' => true,
            ],
            (object) [
                'linetype' => 'plaintransaction',
                'tablelink' => 'gstird',
                'norecurse' => true,
                'reverse' => true,
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
        } else {
            $line->amount = bcadd(@$line->net ?? '0.00', @$line->gst ?? '0.00', 2);
        }
    }

    public function has($line, $assoc)
    {
        if (in_array($assoc, ['gstpeer_gst', 'gstird_gst'])) {
            return @$line->gst != 0;
        }
    }

    public function get_suggested_values($token)
    {
        $suggested_values = [];

        $suggested_values['account'] = get_values($token, 'transaction', 'account');
        $suggested_values['sort'] = ['purchase', 'sale'];

        return $suggested_values;
    }

    public function validate($line)
    {
        $errors = [];

        if ($line->date == null) {
            $errors[] = 'no date';
        }

        if (!@$line->claimdate && $line->gst != 0) {
            $errors[] = 'no claim date';
        }

        if ($line->account == null) {
            $errors[] = 'no account';
        }

        if ($line->net == null && $line->amount == null) {
            $errors[] = 'no monetary entries';
        }

        return $errors;
    }

    public function unpack($line)
    {
        if (@$line->gst != 0) {
            $description = '';

            if (@$line->invert == 'yes') {
                if (@$line->gst < 0) {
                    $description = 'sale';
                } else {
                    $description = 'purchase';
                }
            }

            $line->gstpeer_gst = (object) [
                'date' => $line->date,
                'account' => 'gst',
                'amount' => $line->gst,
                'description' => $description,
                'user' => @$line->user,
            ];
            $line->gstird_gst = (object) [
                'date' => $line->claimdate,
                'account' => 'gst',
                'amount' => 0 - $line->gst,
                'description' => $description,
                'user' => @$line->user,
            ];
        }
    }
}
