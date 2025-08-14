<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = [
        'admin_id', 'name', 'type', 'country', 'tax_rate', 'accounting_method', 'comment', 'is_default',
        'registration_country', 'inn', 'full_name', 'short_name', 'legal_address', 'postal_address',
        'kpp', 'ogrn', 'okpo', 'bik', 'bank_name', 'correspondent_account', 'checking_account',
        'phone', 'email', 'registration_address', 'ogrnip', 'certificate_number', 'certificate_date',
        'card_number', 'snils', 'passport_data',
    ];

    public function calculateTax(float $total): array
    {
        $r = $this->tax_rate;
        switch ($this->accounting_method) {
            case 'osn_inclusive':
                $tax = $r * $total / ($r + 100);
                $payable = $total;
                break;
            case 'osn_exclusive':
                $tax = $r * $total / 100;
                $payable = $total + $tax;
                break;
            case 'usn_inclusive':
                $tax = $r * $total / 100;
                $payable = $total;
                break;
            case 'usn_exclusive':
                $tax = $total / ((100 / $r) - 1);
                $payable = $total + $tax;
                break;
            default:
                $tax = 0;
                $payable = $total;
        }

        return [
            'base' => round($total, 2),
            'tax' => round($tax, 2),
            'payable' => round($payable, 2),
        ];
    }
}
