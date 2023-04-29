<?php

namespace gst\linetype;

class gsttransaction extends \jars\Linetype
{
    public function __construct()
    {
        $this->table = 'transaction';

        $this->simple_string('date');
        $this->simple_string('account');
        $this->simple_string('description');

        $this->fields['invert'] = fn ($records) : bool => ($peer_record = @$records['/gstpeer_gst']) && (
            $peer_record->amount > 0 && $peer_record->description == 'purchase'
            ||
            $peer_record->amount < 0 && $peer_record->description == 'sale'
        );

        $this->fields['gsttype'] = function ($records): ?string {
            if (!$peer_record = @$records['/gstpeer_gst']) {
                return null;
            }

            if ($override = @$peer_record->description) {
                return $override;
            }

            return $peer_record->amount > 0 ? 'sale' : 'purchase';
        };

        $this->fields['claimdate'] = fn ($records): ?string => @$records['/gstird_gst']->date;
        $this->fields['net'] = fn ($records): float => bcadd('0', (string) ($records['/']->amount ?? 0), 2);
        $this->fields['gst'] = fn ($records): ?float => @$records['/gstpeer_gst']->amount ? (float) bcadd('0', $records['/gstpeer_gst']->amount, 2) : null;
        $this->fields['amount'] = fn ($records): float => (float) bcadd((string) ($records['/']->amount ?? 0), (string) ($records['/gstpeer_gst']->amount ?? 0), 2);

        $this->fields['broken'] = function($records) : ?string {
            if (in_array($records['/']->account, ['error', 'correction', 'gst'])) {
                return 'Reserved Account';
            }

            if (isset($records['/gstpeer_gst']) && !isset($records['/gstird_gst'])) {
                throw new \Exception($records['/']->id);
            }

            if (isset($records['/gstpeer_gst']) && $records['/gstpeer_gst']->amount + $records['/gstird_gst']->amount != 0) {
                return 'Unbalanced GST';
            }

            if (isset($records['/gstpeer_gst']) && $records['/gstpeer_gst']->amount != 0 && ($error = round(abs(round($records['/']->amount * 0.15, 2) - $records['/gstpeer_gst']->amount), 2)) > 0.01) {
                return 'GST wrong by ' . var_export($error, 1);
            }

            return null;
        };

        $this->unfuse_fields['amount'] = fn ($line): string => bcadd('0', (string) ($line->net ?? 0), 2);

        $this->inlinelinks = [
            (object) [
                'linetype' => 'peergst',
                'tablelink' => 'gstpeer',
                'property' => 'gstpeer_gst',
            ],
            (object) [
                'linetype' => 'irdgst',
                'tablelink' => 'gstird',
                'property' => 'gstird_gst',
            ],
        ];
    }

    public function complete($line) : void
    {
        if (!@$line->claimdate) {
            $m = sprintf('%02d', (floor(substr($line->date, 5, 2) / 2) * 2 + 11) % 12 + 1);
            $y = date('Y', strtotime($line->date)) - ($m > date('m', strtotime($line->date)) ? 1 : 0);
            $line->claimdate = date('Y-m-d', strtotime('+3 month -1 day', strtotime("$y-$m-01")));
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

        if (!@$line->claimdate && (float) @$line->gst) {
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
        if ((float) @$line->gst) {
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
                'amount' => bcadd('0', (string) ($line->gst ?? 0), 2),
                'description' => $description,
                'user' => @$line->user,
            ];

            $line->gstird_gst = (object) [
                'date' => $line->claimdate,
                'account' => 'gst',
                'amount' => bcsub('0', (string) ($line->gst ?? 0), 2),
                'description' => $description,
                'user' => @$line->user,
            ];
        }
    }
}
