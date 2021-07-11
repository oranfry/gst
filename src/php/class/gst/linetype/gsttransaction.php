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
            'icon' => function($records) : string {
                return 'dollar';
            },
            'date' => function($records) : string {
                return $records['/']->date;
            },
            'account' => function($records) : string {
                // if (!@$records['/']->account) {
                //     echo "gsttransaction: no account (?!)\n";
                //     var_dump($records);
                //     die();
                // }

                return @$records['/']->account ?: 'unknown';
            },
            'description' => function($records) : ?string {
                return @$records['/']->description;
            },
            'invert' => function($records) : ?string {
                if (!isset($records['/gstpeer_gst'])) {
                    return null;
                }

                return [true => 'yes', false => 'no'][
                    $records['/gstpeer_gst']->amount > 0 and $records['/gstpeer_gst']->description == 'purchase'
                    ||
                    $records['/gstpeer_gst']->amount < 0 and $records['/gstpeer_gst']->description == 'sale'
                ];
            },
            'claimdate' => function($records) : ?string {
                return @$records['/gstird_gst']->date;
            },
            'hasgst' => function($records) : ?string {
                return @$records['/gstpeer_gst']->amount != 0 ? 'moneytake' : null;
            },
            'net' => function($records) : string {
                return @$records['/']->amount ?: '0.00';
            },
            'gst' => function($records) : ?string {
                return @$records['/gstpeer_gst']->amount;
            },
            'amount' => function($records) : string {
                if (!property_exists($records['/'], 'amount')) {
                    echo "gsttransaction: no amount (?!)\n";
                    var_dump($records);
                    die();
                }

                return (@$records['/']->amount ?? 0) + (@$records['/gstpeer_gst']->amount ?? 0);
            },
            // 'file' => function($records) : string {
            //     'path' => 'transaction',
            // },
            'broken' => function($records) : ?string {
                if (in_array($records['/']->account, ['error', 'correction', 'gst'])) {
                    return 'Reserved Account';
                }

                if (isset($records['/gstpeer_gst']) && $records['/gstpeer_gst']->amount + $records['/gstird_gst']->amount != 0) {
                    return 'Unbalanced GST';
                }

                if (isset($records['/gstpeer_gst']) && $records['/gstpeer_gst']->amount != 0 && abs(round($records['/']->amount * 0.15, 2) - $records['/gstpeer_gst']->amount) > 0.01) {
                    return 'Wrong GST';
                }

                return null;
            },
        ];

        $this->unfuse_fields = [
            'date' => function($line) : string {
                return $line->date;
            },
            'account' => function($line) : string {
                return $line->account;
            },
            'description' => function($line) : ?string {
                return @$line->description;
            },
            'amount' => function($line) : string {
                return @$line->net ?? '0';
            },
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

        if (!@$line->net && @!$line->amount) {
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
