<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FavoritesController extends Controller
{
    public function store($micropostId)
    {
    //　認証済みユーザが、投稿されたmicropostをお気に入り登録する
    \Auth::user()->favorite($micropostId);
    return back();
    }
    
    public function destroy($micropostId)
    {
        \Auth::user()->unfavorite($micropostId);
        return back();
    }
}
