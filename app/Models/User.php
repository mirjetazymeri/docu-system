<?php

namespace App\Models;

use Cartalyst\Sentinel\Users\EloquentUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Cviebrock\EloquentTaggable\Taggable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Str;

class User extends EloquentUser
{

    /**
     * The attributes to be fillable from the model.
     *
     * A dirty hack to allow fields to be fillable by calling empty fillable array
     *
     * @var array
     */
    use HasApiTokens, HasFactory, Notifiable, Taggable;

    /**
     * To allow soft deletes
     */
    use SoftDeletes;
    /**
     * The database table used by the model.
     *
     * @var string
     */

    protected $table = 'users';

    protected $fillable = [];
    protected $guarded = ['id'];
    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token'];

    protected $dates = ['deleted_at'];

    protected $appends = ['full_name'];
    public function getFullNameAttribute()
    {
        return Str::limit($this->first_name . ' ' . $this->last_name, 30);
    }
    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function accessLevels()
    {
        return $this->belongsToMany(AccessLevel::class, 'user_access_levels');
    }

    public function applications()
    {
        return $this->belongsToMany(Application::class, 'application_user')
            ->withPivot('can_edit')
            ->withTimestamps();
    }

    public function createdDocumentations()
    {
        return $this->hasMany(Documentation::class, 'created_by');
    }

    public function documentationWorkerHistories()
    {
        return $this->hasMany(DocumentationWorkerHistory::class);
    }

    public function hasAccess($slug)
    {
        $permissions = $this->accessLevels()
            ->with('permissions')
            ->get()
            ->pluck('permissions')
            ->flatten()
            ->pluck('slug')
            ->unique();

        return $permissions->contains($slug);
    }

    public function employee()
    {
        return $this->hasOne(Employee::class);
    }

    public function customer()
    {
        return $this->hasOne(Customer::class);
    }

    // Define relationship with EmployeeReview as a reviewer
    public function reviews()
    {
        return $this->hasMany(EmployeeReview::class, 'reviewer_id');
    }

}





