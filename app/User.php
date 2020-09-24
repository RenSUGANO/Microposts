<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    
    /**
     * このユーザが所有する投稿。（ Micropostモデルとの関係を定義）
     */
    public function microposts()
    {
        return $this->hasMany(Micropost::class);
    }
    
        public function loadRelationshipCounts()
    {
        $this->loadCount(['microposts', 'followings', 'followers', 'favorites']);
    }
    
    public function followings()
    {
        return $this->belongsToMany(User::class, 'user_follow', 'user_id', 'follow_id')->withTimestamps();
    }
    
    public function followers()
    {
        return $this->belongsToMany(User::class, 'user_follow', 'follow_id', 'user_id')->withTimestamps();
    }
    
    public function follow($userId)
    {
        //既にフォローしているかの確認
        $exist = $this->is_following($userId);
        // 相手が自分自身かどうかの確認
        $its_me = $this->id == $userId;
        
        if ($exist || $its_me){
            // 既にフォローしていれば何もしない
            return false;
        } else {
            // 未フォローであればフォローする
            $this->followings()->attach($userId);
            return true;
        }
    }
    
    public function unfollow($userId)
    {
        //既にフォローしているかの確認
        $exist = $this->is_following($userId);
        //　相手が自分自身かどうかの確認
        $its_me = $this->id == $userId;
        
        if ($exist && !$its_me) {
            //　既にフォローしていればフォローを外す
            $this->followings()->detach($userId);
            return true;
        }else {
            //未フォローであれば何もしない
            return false;
        }
    }
    
    public function is_following($userId)
    {
        //フォロー中ユーザの中に$userIdのものが存在するか
        return $this->followings()->where('follow_id', $userId)->exists();
    }
    
    public function feed_microposts()
    {
        $userIds = $this->followings()->pluck('users.id')->toArray();
        $userIds[] = $this->id;
        return Micropost::whereIn('user_id', $userIds);
    }
    
    public function favorites()
    {
        return $this->belongsToMany(Micropost::class, 'favorites', 'user_id', 'micropost_id')->withTimestamps();
    }
    
    public function favorite($micropostId)
    {
        // dd($micropostId);
        
        //　すでにお気に入りしているかの確認
        $exist = $this->is_favorite($micropostId);
        
        if ($exist) {
            //　すでにお気に入りにしていれば何もしない
            return false;
        } else {
            // お気に入りしていなけれがお気に入りする
            $this->favorites()->attach($micropostId);
            
            return true;
        }
    }
    
    public function unfavorite($micropostId)
    {
        //　すでにお気に入りしているかの確認
        $exist = $this->is_favorite($micropostId);
        
        if ($exist) {
            //　既にお気に入りにしていればお気に入りを外す
            $this->favorites()->detach($micropostId);
            return true;
        } else {
            //　お気に入りにしていないのなら何もしない
            return false;
        }
    }
    
    public function is_favorite($micropostId)
    {
        //　お気に入り中の中に$micropostIdのものが存在するか
        return $this->favorites()->where('micropost_id', $micropostId)->exists();
    }
}



// フォロー機能
// usersテーブル　→　usersテーブル　を参照する。　中間テーブルは、 user_followです。　カラムは user_idです。　もう一つのカラムは follow_idです。


// お気に入り機能
// usersテーブル　→　○○テーブル　を参照する。　中間テーブルは、 △△テーブルです。　カラムは □□です。　もう一つのカラムは ☆☆です。

