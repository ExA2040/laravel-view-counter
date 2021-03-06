<?php

namespace ExA2040\LaravelViewCounter;

trait ViewCounterTrait {

  /**
   * Return the most liked content
   *
   * @return Integer
   */
  public function scopeMostLiked($query, $limit = false)
  {
    return $query->leftJoin('counter','counter.object_id','=',$this->getTable().'.id')
        ->whereClassName(snake_case(get_class($this)))
        ->orderBy('counter.like_counter', 'DESC');
  }

  /**
   * Return the most viewed content
   *
   * @return Integer
   */
  public function scopeMostViewed($query, $limit = false)
  {
    return $query->leftJoin('counter','counter.object_id','=',$this->getTable().'.id')
        ->whereClassName(snake_case(get_class($this)))
        ->orderBy('counter.view_counter', 'DESC');
  }

  public function counter()
  {
    return $this->hasOne('\ExA2040\LaravelViewCounter\Counter', 'object_id')->where('class_name', snake_case(get_class($this)));
  }

  public function user_counters()
  {
    return $this->hasMany('\ExA2040\LaravelViewCounter\UserCounter', 'object_id')->where('class_name', snake_case(get_class($this)));
  }

  /**
   * Return authentificated users who viewed we know
   *
   * @return Integer
   */
  public function views()
  {

  }

  public function view()
  {
    if(!$this->isViewed())
    {
      if(\Auth::user())
      {
        $sessions = \Session::all();
        foreach ($sessions as $key => $session){
          if( explode("_", $key)[0] === 'viewed'){
            $this->user_counters()->firstOrCreate(array(
                'class_name' => snake_case(get_class($this)),
                'object_id'  => $this->id,
                'user_id'    => \Auth::user()->id,
                'action'     => 'view'
            ));
          }
        }
        $this->user_counters()->firstOrCreate(array(
            'class_name' => snake_case(get_class($this)),
            'object_id' => $this->id,
            'user_id' => \Auth::user()->id,
            'action' => 'view'
        ));
      }
      \Session::put($this->get_view_key(), time());
      if(!isset($this->counter))
      {
        $class_name = snake_case(get_class($this));
        \ExA2040\LaravelViewCounter\Counter::firstOrCreate(array('class_name' => $class_name, 'object_id' => $this->id));
      }
      $this->counter()->increment('view_counter');
      return true;
    }
    return false;
  }

  /**
   * Return views count
   *
   * @return Integer
   */
  public function views_count()
  {
    return !empty($this->counter->view_counter)?$this->counter->view_counter:0;
  }

  /**
   * Is object already viewed by user?
   *
   * @return Boolean
   */
  public function isViewed()
  {
    if(!\Auth::user())
    {
      $viewed = \Session::get($this->get_view_key());
      if(!empty($viewed)) {
        return true;
      }
    } else {
      $user_action = $this->user_counters()
          ->where('action', 'view')
          ->where('class_name', snake_case(get_class($this)))
          ->where('object_id', $this->id)
          ->where('user_id', \Auth::user()->id)
          ->count();
      if($user_action > 0)
        return true;
    }
    return false;
  }

  /**
   * get session storage key for view
   *
   * @return String
   */
  private function get_view_key()
  {
    return 'viewed_'.snake_case(get_class($this)).'_'.$this->id;
  }

  /**
   * Return authentificated users who liked we know
   *
   * @return Integer
   */
  public function likes()
  {

  }

  /**
   * Do a like on this object
   * returns success or failure
   *
   * @return Boolean
   */
  public function like()
  {
    if(!$this->isLiked())
    {
      if(\Auth::user())
      {
        $this->user_counters()->create(array(
            'class_name' => snake_case(get_class($this)),
            'object_id' => $this->id,
            'user_id' => \Auth::user()->id,
            'action' => 'like'
        ));
        $this->counter()->increment('like_counter');

        return true;
      } else {
        \Session::put($this->get_like_key(), time());
        $this->counter()->increment('like_counter');

        return true;
      }
    }
    return false;
  }

  /**
   * Unlike on this object
   * returns success or failure
   *
   * @return Boolean
   */
  public function unlike()
  {
    if($this->isLiked())
    {
      if(\Auth::user())
      {
        $this->user_counters()->where(array(
            'class_name' => snake_case(get_class($this)),
            'object_id' => $this->id,
            'user_id' => \Auth::user()->id,
            'action' => 'like'
        ))->delete();
        $this->counter()->decrement('like_counter');

        return true;
      } else {
        \Session::forget($this->get_like_key());
        $this->counter()->decrement('like_counter');

        return true;
      }
    }
    return false;
  }

  /**
   * Return likes count
   *
   * @return Integer
   */
  public function likes_count()
  {
    return $this->counter()->like_counter;
  }

  /**
   * Is object already liked by user?
   *
   * @return Boolean
   */
  public function isLiked()
  {
    if(!\Auth::user())
    {
      $viewed = \Session::get($this->get_like_key());
      if(!empty($viewed)) {
        return true;
      }
    } else {
      $user_action = $this->user_counters()
          ->where('action', 'like')
          ->where('class_name', snake_case(get_class($this)))
          ->where('object_id', $this->id)
          ->where('user_id', \Auth::user()->id)->count();
      if($user_action > 0)
        return true;
    }
    return false;
  }

  /**
   * get session storage key for like
   *
   * @return String
   */
  private function get_like_key()
  {
    return 'liked_'.snake_case(get_class($this)).'_'.$this->id;
  }

}
