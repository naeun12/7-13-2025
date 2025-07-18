<?php

namespace App\Models\landlord;
use App\Models\tenant\tenantModel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Models\landlord\landlordDormManagement; // Assuming you have a Dorm model for dorm management

class roomModel extends Model
{
    use  HasFactory, Notifiable;  // Add HasApiTokens here
    protected $table = 'rooms';
    protected $primaryKey = 'roomID';
    public $timestamps = true;
  

    protected $fillable = [
        'roomID',
        'fkdormID',
        'fklandlordID',
        'roomNumber',
        'roomType',
        'availability',
        'price',
        'furnishing_status',
        'listingType',
        'areaSqm',
        'genderPreference',
        'roomImages',
        'created_at',
        
    ];
    public function landlord()
{
    return $this->belongsTo(\App\Models\landlord\landlordModel::class, 'landlordID');
}

    public function dorm()
{
    return $this->belongsTo(dormModel::class, 'fkdormID', 'dormID');
}
public function tenant()
{
    return $this->belongsTo(tenantModel::class, 'tenantID');
}
public function features()
{
    return $this->belongsToMany(
        'App\Models\landlord\featuresModel', // Adjust the namespace as needed
        'roomfeaturesrooms', // Pivot table name
        'fkroomID', // Foreign key in the pivot table
        'fkfeatureID' // Foreign key in the pivot table
    )->withPivot('fkroomID', 'fkfeatureID'); // Include pivot timestamps if needed
}
public function currentTenant()
{
    return $this->hasOne(\App\Models\tenant\approvetenantsModel::class, 'fkroomID', 'roomID');
            
}


}

