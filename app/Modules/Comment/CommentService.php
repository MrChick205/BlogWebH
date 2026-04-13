<?php
namespace App\Modules\Comment;

class CommentService
{
    public function __construct(private CommentRepository $repository)
    {
    }

    public function getCommentsByPost($postId, $perPage = 20)
    {
        return $this->repository->findByPost($postId, $perPage);
    }

    public function createComment($postId, $userId, array $data)
    {
        return $this->repository->create([
            'post_id' => $postId,
            'user_id' => $userId,
            'content' => $data['content'],
        ]);
    }

    public function getCommentById($id)
    {
        return $this->repository->find($id);
    }

    public function updateComment($postId, $userId, array $data)
    {
        $comment = $this->repository->find($postId);
        if (!$comment) {
            throw new \Exception('Comment not found');
        }

        if ($comment->user_id !== $userId) {
            throw new \Exception('You are not authorized to update this comment');
        }

        return $this->repository->update($postId, [
            'content' => $data['content'],
        ]);
    }

    public function deleteComment($id)
    {
        return $this->repository->delete($id);
    }
}