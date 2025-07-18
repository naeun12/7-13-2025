<?php

namespace App\Models\tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\landlord\reservationModel;

class reservationpaymentModel extends Model
{
    use HasFactory;

    protected $table = 'reservationpayments';
    protected $primaryKey = 'paymentID';
    public $incrementing = true;

    protected $fillable = [
        'reservationID',
        'paymentType',
        'paymentImage',
        'status'
    ];

    public function reservation()
    {
        return $this->belongsTo(reservationModel::class, 'reservationID', 'reservationID');
    }
}
