<?php

namespace gst\linetype;

class gsttransaction extends \jars\Linetype
{
    public function __construct()
    {
        $this->table = 'transaction';

        $this->fields = [
            'date' => fn ($records) : string => $records['/']->date,
            'account' => fn ($records) : string => @$records['/']->account ?: 'unknown',
            'description' => fn ($records) : ?string => @$records['/']->description,
            'invert' => fn ($records) : bool => ($peer_record = @$records['/gstpeer_gst']) && (
                $peer_record->amount > 0 && $peer_record->description == 'purchase'
                ||
                $peer_record->amount < 0 && $peer_record->description == 'sale'
            ),
            'claimdate' => fn ($records) : ?string => @$records['/gstird_gst']->date,
            'net' => fn ($records) : float => (float) (@$records['/']->amount ?: '0.00'),
            'gst' => fn ($records) : ?float => @$records['/gstpeer_gst']->amount ? (float) $records['/gstpeer_gst']->amount : null,
            'amount' => fn ($records) : float => (float)((@$records['/']->amount ?? 0) + (@$records['/gstpeer_gst']->amount ?? 0)),
            'is_peergst' => fn ($records) : bool => (bool) @$records['/gstpeer_transaction']->id,
            'broken' => function($records) : ?string {
                if (in_array($records['/']->account, ['error', 'correction', 'gst'])) {
                    return 'Reserved Account';
                }

                if (isset($records['/gstpeer_gst']) && $records['/gstpeer_gst']->amount + $records['/gstird_gst']->amount != 0) {
                    return 'Unbalanced GST';
                }

                if (isset($records['/gstpeer_gst']) && $records['/gstpeer_gst']->amount != 0 && ($error = round(abs(round($records['/']->amount * 0.15, 2) - $records['/gstpeer_gst']->amount), 2)) > 0.01) {
                    return 'GST wrong by ' . var_export($error, 1);
                }

                return null;
            },
        ];

        $this->unfuse_fields = [
            'date' => fn ($line) : string => $line->date,
            'account' => fn ($line) : string => $line->account,
            'description' => fn ($line) : ?string => @$line->description,
            'amount' => fn ($line) : string => @$line->net ?? '0',
        ];

        $this->inlinelinks = [
            (object) [
                'linetype' => 'plaintransaction',
                'tablelink' => 'gstpeer',
                'property' => 'gstpeer_gst',
            ],
            (object) [
                'linetype' => 'plaintransaction',
                'tablelink' => 'gstird',
                'property' => 'gstird_gst',
            ],
            (object) [
                'linetype' => 'plaintransaction',
                'property' => 'gstpeer_transaction',
                'tablelink' => 'gstpeer',
                'reverse' => true,
            ],
        ];
    }

    public function complete($line) : void
    {
        if (!@$line->claimdate) {
            $m = sprintf('%02d', (floor(substr($line->date, 5, 2) / 2) * 2 + 11) % 12 + 1);
            $y = date('Y', strtotime($line->date)) - ($m > date('m', strtotime($line->date)) ? 1 : 0);
            $line->claimdate = date_shift("$y-$m-01", "+3 month -1 day");
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

    public function unpack($line, $oldline, $old_gstird_transactions)
    {
        if (@$line->gst != 0) {
            $description = '';

            if (@$line->invert) {
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

            $line->gstpeer_transaction = 'unchanged';
        }
    }
}
