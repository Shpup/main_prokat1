<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Counterparty extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'name',
        'manager_id',
        'code',
        'status',
        'actual_address',
        'comment',
        'is_available_for_sublease',
        'type',
        'registration_country',
        'inn',
        'full_name',
        'short_name',
        'legal_address',
        'postal_address',
        'kpp',
        'ogrn',
        'okpo',
        'bik',
        'bank_name',
        'correspondent_account',
        'checking_account',
        'phone',
        'email',
        'registration_address',
        'ogrnip',
        'certificate_number',
        'certificate_date',
        'card_number',
        'snils',
        'passport_data',
    ];

    protected $casts = [
        'is_available_for_sublease' => 'boolean',
    ];

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
