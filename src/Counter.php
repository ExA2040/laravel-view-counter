<?php

namespace ExA2040\LaravelViewCounter;
use Illuminate\Database\Eloquent\Model;

class Counter extends Model {

  protected $table = 'counters';
  protected $fillable = array('class_name', 'object_id');

}
