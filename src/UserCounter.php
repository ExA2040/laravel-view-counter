<?php

namespace ExA2040\LaravelViewCounter;
use Illuminate\Database\Eloquent\Model;
class UserCounter extends Model {

  protected $table = 'user_counters';
  protected $fillable = array('class_name', 'object_id', 'user_id', 'action');

}
