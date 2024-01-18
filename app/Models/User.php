<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Model;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;
    use HasRoles , SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

    //protected $with = ['department'];
    protected $fillable = [
        'name',
        'email',
        'password',
        'first_name',
        'last_name',
        'role',
        'department_id',
        'pin',
        'provider_id',
        'provider_name',
        'google_access_token_json',
    ];


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
    public function attendance()
    {
        return $this->hasMany('App\Models\Attendance', 'pin', 'pin');
    }
    public function contract()
    {
        return $this->hasMany(Contract::class, 'user_id');
    }
    public function department()
    {
        return $this->belongsTo('App\Models\Department');
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function my_decisions()
    {
        return $this->hasMany(Decision::class, 'user_id', 'id');
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }


    public function my_contacts()
    {
        return $this->hasMany(Contact::class, 'user_id', 'id');
    }

    public function Requests()
    {
        return $this->hasMany(Request::class, 'user_id');
    }
    // public function getRoleAttribute()
    // {
    //     return $this->getRoleNames()->first();
    // }

    public function userRates()
    {
        return $this->hasMany(Rate::class, 'user_id');
    }
    public function evaluatorRates()
    {
        return $this->hasMany(Rate::class, 'evaluator_id');
    }
<<<<<<< HEAD
=======
    public function  absences ()
    {
        return $this->hasMany(Absences::class, 'user_id');

    }
>>>>>>> b5419de8d28d4ad44bcb11dc43d3d786296a2352




    public function userInfo()
    {
        return $this->hasOne(UserInfo::class);
    }
    public function address()
    {
        return $this->hasOne(Address::class);
    }
    public function careers()
    {
        return $this->hasMany(Career::class);
    }
    public function deposits()
    {
        return $this->hasMany(Deposit::class);
<<<<<<< HEAD
    }
    public function  absences()
    {
        return $this->hasMany(Absences::class, 'user_id');
    }
    public function  notes()
    {
        return $this->hasMany(Note::class);
    }
    public function  languages()
    {
        return $this->hasMany(Language::class);
    }
    public function  certificates()
    {
        return $this->hasMany(Certificate::class);
    }
    public function  study_situations()
    {
        return $this->hasMany(StudySituation::class);
=======
>>>>>>> b5419de8d28d4ad44bcb11dc43d3d786296a2352
    }

   

}
