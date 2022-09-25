<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Forum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use PhpParser\Node\Stmt\TryCatch;

class CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $forum_id)
    {
        $validator = Validator::make(request()->all(), [
            'body' => 'required',
        ]);

        if ($validator->fails()) {
            response()->json($validator->messages(), 422)->send();
            exit;
        }

        // Cek login
        $user = $this->getAuthUser();

        // Tambah di database
        $user->comment()->create([
            'forum_id' => $forum_id,
            'body' => request('body')
        ]);

        return response()->json(['message' => 'Komen berhasil dibuat']);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $forum_id, $comment_id)
    {
        $validator = Validator::make(request()->all(), [
            'body' => 'required',
        ]);

        if ($validator->fails()) {
            response()->json($validator->messages(), 422)->send();
            exit;
        }

        // Cek kepemilikan
        $comment = Comment::find($comment_id);
        $this->checkOwnership($comment->user_id);

        // Ubah di database
        $comment->update([
            'body' => request('body')
        ]);

        return response()->json(['message' => 'Komen berhasil diubah']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($forum_id, $comment_id)
    {
        // Cek kepemilikan
        $comment = Comment::find($comment_id);

        if ($comment) {
            $this->checkOwnership($comment->user_id);
        } else{
            response()->json(['message' => 'Komentar tidak ditemukan'])->send();
            exit;
        }

        // Hapus di database
        $comment->delete();

        return response()->json(['message' => 'Komen berhasil dihapus']);
    }

    private function getAuthUser()
    {
        try {
            return auth()->userOrFail();
        } catch (\Tymon\JWTAuth\Exceptions\UserNotDefinedException $th) {
            response()->json(['message' => 'Not Authenticated, You have to login first'])->send();
            exit;
        }
    }

    private function checkOwnership($owner)
    {
        $user = $this->getAuthUser();
        if ($user->id != $owner) {
            response()->json(['message' => 'Not authorized'], 403)->send();
            exit;
        }
    }
}
