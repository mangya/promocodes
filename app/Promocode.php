<?php

namespace App;

use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\Model;

class Promocode extends Model
{
	/**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code','discount','max_discount','validity_radius','validity_radius_unit','is_active','expires_at'
    ];

    /**
     * Validation rules for new promocode
     * 
     * @return Array
     */
	public static function getValidationRules() 
	{
		return [
		    'discount' => 'required|integer|max:100',
		    'max_discount' => 'required|integer',
		    'validity_radius' => 'required|integer',
		    'validity_radius_unit' => Rule::in(['kms', 'miles']),
		    'expires_in' => 'required|integer'
		];
	}

	/**
	 * Generate new code for promocode
	 * 
	 * @return string
	 */
	public function generateCode(): string
    {
	    $code_str = strtoupper(Str::random(6));

	    if ($this->codeExists($code_str)) {
	        return generateCode();
	    }

	    return $code_str;
	}

	/**
	 * Check if generated code already exists in DB
	 * 
	 * @param  string $str
	 * @return bool     
	 */
	private function codeExists($str): bool
	{
	    return Promocode::whereCode($str)->exists();
	}
}
