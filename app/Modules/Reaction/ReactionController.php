<?php
namespace App\Modules\Reaction;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Modules\Reaction\ReactionService;

class ReactionController
{
    protected $reactionService;

    public function __construct(ReactionService $reactionService)
    {
        $this->reactionService = $reactionService;
    }

    public function index($postId)
    {
        return response()->json(
            $this->reactionService->findByPost($postId)
        );
    }

    public function store($postId, Request $request)
    {
        $type = $request->input('type', 'like');

        return response()->json(
            $this->reactionService->react(
                $postId,
                $request->user()->id,
                $type
            ),
            201
        );
    }

    public function destroy($postId, Request $request)
    {
        return response()->json(
            $this->reactionService->unreact(
                $postId,
                $request->user()->id
            ),
            204
        );
    }
}