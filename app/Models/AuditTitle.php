<?php



namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditTitle extends Model
{
    use HasFactory;

    protected $fillable = ['name']; // Adjust fields as needed
}
