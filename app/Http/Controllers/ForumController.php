<?php

namespace App\Http\Controllers;

use App\Models\Forum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ForumController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Forum::with('user')->withCount('comment')->paginate(3);
    }
    
    
    public function filterTag($tag)
    {
        return Forum::where('category', $tag)->with('user')->withCount('comment')->paginate(3);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Cek validasi data
        $validator = Validator::make(request()->all(), [
            'title' => 'required',
            'body' => 'required',
            'category' => 'required',
        ]);
        if ($validator->fails()) {
            response()->json($validator->messages(), 422)->send();
            exit;
        }

        // Cek login
        $user = $this->getAuthUser();

        // simpan ke database
        Forum::create([
            'user_id' => $user->id,
            'title' => request('title'),
            'category' => request('category'),
            'body' => request('body'),
        ]);

        return response()->json(['message' => 'Forum berhasil disimpan']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return Forum::with('user', 'comment')->find($id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // Cek validasi data
        $validator = Validator::make(request()->all(), [
            'title' => 'required',
            'body' => 'required',
            'category' => 'required',
        ]);
        if ($validator->fails()) {
            response()->json($validator->messages(), 422)->send();
            exit;
        }

        // check ownership
        $forum = Forum::find($id);
        $this->checkOwnership($forum->user_id);

        // Ubah ke database
        $forum->update([
            'title' => request('title'),
            'category' => request('category'),
            'body' => request('body'),
        ]);

        return response()->json(['message' => 'Forum berhasil diubah']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // check ownership
        $forum = Forum::find($id);
        $this->checkOwnership($forum->user_id);

        // Hapus ke database
        $forum->delete();

        return response()->json(['message' => 'Forum berhasil dihapus']);
    }

    private function getAuthUser()
    {
        try {
            return auth()->userOrFail();
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $th) {
            response()->json(['message' => 'Harus login terlebih dahulu'])->send();
            exit;
        }
    }

    private function checkOwnership($owner)
    {
        $user = $this->getAuthUser();
        if ($user->id != $owner) {
            response()->json(['message' => 'Not Authorized'], 403)->send();
            exit;
        }
    }
}
