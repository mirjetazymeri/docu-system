<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// use Spatie\Activitylog\Traits\LogsActivity;
// use Spatie\Activitylog\LogOptions;
// use Spatie\Activitylog\Contracts\Activity;
// use Sentinel; // Import Sentinel

class Employee extends BaseModel
{

    protected $fillable = [
        'user_id',
        'position',
        'department',
        'salary',
        'hire_date',
        'manager_id',
        'dob',
        'full_name',
        'gender',
        'nationality',
        'marital_status',
        'national_id',
        'employment_type',
        'email',
        'work_email',
        'primary_phone',
        'secondary_phone',
        'address',
        'city',
        'state',
        'postal_code',
        'country',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relationship',
        'bank_name',
        'bank_account_number',
        'bank_account_name',
        'bank_branch',
        'bank_ifsc_code',
        'resume',
        'id_proof',
        'address_proof',
        'offer_letter'
    ];

    protected function getLogAttributes(): array
    {
        return [
            'user_id',
            'position',
            'department',
            'salary',
            'hire_date',
            'manager_id',
            'dob',
            'full_name',
            'gender',
            'nationality',
            'marital_status',
            'national_id',
            'employment_type',
            'email',
            'work_email',
            'primary_phone',
            'secondary_phone',
            'address',
            'city',
            'state',
            'postal_code',
            'country',
            'emergency_contact_name',
            'emergency_contact_phone',
            'emergency_contact_relationship',
            'bank_name',
            'bank_account_number',
            'bank_account_name',
            'bank_branch',
            'bank_ifsc_code',
            'resume',
            'id_proof',
            'address_proof',
            'offer_letter'
        ];
    }

    /**
     * Get the options for activity logging.
     */
    // public function getActivitylogOptions(): LogOptions
    // {
    //     return LogOptions::defaults()
    //         ->logOnly(['user_id', 'position', 'department', 'salary', 'hire_date', 'manager_id'])
    //         ->logOnlyDirty()
    //         ->setDescriptionForEvent(fn(string $eventName) => "This model has been {$eventName}");
    // }

    // public function tapActivity(Activity $activity, string $eventName)
    // {
    //     // $activity->causer_id = Sentinel::getUser()->id : null;
    //     $activity->causer_id = Sentinel::getUser()->id ?? null;
    // }

    // Define relationship with User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Define relationship with manager (who is also an employee)
    public function manager()
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }

    // Define relationship with subordinates
    public function subordinates()
    {
        return $this->hasMany(Employee::class, 'manager_id');
    }

    // Define relationship with contracts
    public function contracts()
    {
        return $this->hasMany(Contract::class);
    }

    // Define relationship with employee leaves
    public function leaves()
    {
        return $this->hasMany(EmployeeLeave::class);
    }

    // Define relationship with vehicle accidents
    public function accidents()
    {
        return $this->hasMany(VehicleAccident::class);
    }

    public function reviews()
    {
        return $this->hasMany(EmployeeReview::class);
    }
}
