<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Model implements AuthenticatableContract, AuthorizableContract, JWTSubject
{
    use Authenticatable, Authorizable, HasFactory;
    
    protected $table = 'user';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'id',
        'active',
        'sys_level',
        'sys_role',
        'person_name',
        'username',
        'birthdate'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var string[]
     */
    protected $hidden = [
        'password',
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
    
    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * Return a products array.
     *
     * @return array
     */
    public function products()
    {
        return $this->hasMany(Product::class, 'id_seller');
    }

    /**
     * Return a transactions array.
     *
     * @return array
     */
    public function transactionsOfBuy()
    {
        return $this->hasMany(Transaction::class, 'id_buyer');
    }

    /**
     * Return a transactions array.
     *
     * @return array
     */
    public function transactionsOfSell()
    {
        return $this->hasMany(Transaction::class, 'product_id')
            ->whereHas('product', function ($query) {
                $query->where('id_seller', $this->id);
            });
    }
}
