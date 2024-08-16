<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReactionType extends Model
{
    use HasFactory;

    // The Eloquent convention for the tables names is to put an "s" at the end
    // of the model name. But in this case, with a multi-word model name, the
    // first word must also be plural to stay grammatically correct.
    // As this breaks the automation of Eloquent, the table name is explicitly
    // specified here.
    protected $table = 'reactions_types';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    public function reactions()
    {
        return $this->hasMany(Reaction::class, 'type_id');
    }
}
