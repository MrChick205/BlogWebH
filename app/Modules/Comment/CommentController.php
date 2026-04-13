<?php   
namespace App\Modules\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Modules\Comment\CommentService;

class CommentController
{
    public function __construct(private CommentService $service)
    {
    }

    public function index($postId)
    {
        return response()->json($this->service->getCommentsByPost($postId));
    }

    public function store($postId, Request $request)
    {
        return response()->json(
            $this->service->createComment(
                $postId,
                $request->user()->id,
                $request->all(),
            ), 201);
    }

    public function show($id)
    {
        return response()->json($this->service->getCommentById($id));
    }

    public function update($id, Request $request)
    {
        return response()->json(
            $this->service->updateComment(
                $id,
                $request->user()->id,
                $request->all()
            )
        );
    }

    public function destroy($id)
    {
        $this->service->deleteComment($id);
        return response()->json(null, 204);
    }
}